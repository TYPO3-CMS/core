.. include:: /Includes.rst.txt

.. _feature-99491-1771891200:

============================================================
Feature: #99491 - PSR-14 event for redirect integrity checks
============================================================

See :issue:`99491`

Description
===========

A new PSR-14 event :php:`\TYPO3\CMS\Redirects\Event\RedirectIntegrityCheckEvent`
has been added. It is dispatched in
:php:`\TYPO3\CMS\Redirects\Service\IntegrityService->checkRedirectTargetIntegrity()`
for each redirect record.

While the existing integrity check only verifies whether redirect **sources**
conflict with existing page URLs (self-reference), this event allows extensions
to validate redirect for other conflict types. For example, an extension can
check whether a ``t3://record`` link target still resolves or whether an external
URL returns a valid response.

The event provides the following methods:
-   :php:`getRedirect()`: The full :sql:`sys_redirect` record as an array.
-   :php:`getUid()`: Convenience method returning the redirect `uid` integer.
-   :php:`getPid()`: Convenience method returning the redirect `pid` integer.
-   :php:`getDeleted()`: Convenience method returning the redirect `deleted` boolean.
-   :php:`getDisabled()`: Convenience method returning the redirect `disabled` boolean.
-   :php:`getSourceHost()`: Convenience method returning the redirect `source_host` string.
-   :php:`getSourcePath()`: Convenience method returning the redirect `source_path` string.
-   :php:`getIsRegExp()`: Convenience method returning the redirect `is_regexp` boolean.
-   :php:`getProtected()`: Convenience method returning the redirect `protected` boolean.
-   :php:`getForceHttps()`: Convenience method returning the redirect `force_https` boolean.
-   :php:`getRespectQueryParameters()`: Convenience method returning the redirect `respect_query_parameters` boolean.
-   :php:`getKeepQueryParameters()`: Convenience method returning the redirect `keep_query_parameters` boolean.
-   :php:`getTarget()`: Convenience method returning the redirect `target` string.
-   :php:`getTargetStatusCode()`: Convenience method returning the redirect `target_statuscode` integer.
-   :php:`getCreationType()`: Convenience method returning the redirect `creation_type` integer.
-   :php:`getOriginalIntegrityStatus()`: Convenience method returning the redirect `integrity_status` string.
-   :php:`getIntegrityStatus()` / :php:`setIntegrityStatus()`: Read or set the
    integrity status. When a listener sets a non-null status, the redirect is
    reported as a conflict in the :bash:`redirects:checkintegrity` command output.
    **Be aware** that :php:`\TYPO3\CMS\Redirects\Utility\RedirectConflict::NO_CONFLICT`
    is possible set as integrity status and will not be included in the report.
    Even listener does not take care to set :php-short:`\TYPO3\CMS\Redirects\Utility\RedirectConflict::NO_CONFLICT`
    for the redirect.

Additionally, following new class constants are added to allow a convenient and
shared reuse of conflict status for extensions developers to set in custom
event listeners:

* :php:`\TYPO3\CMS\Redirects\Utility\RedirectConflict::INVALID_TARGET`

Example
=======

An event listener that validates ``t3://record`` targets:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/ValidateRedirectTarget.php

    <?php

    namespace MyVendor\MyExtension\EventListener;

    use TYPO3\CMS\Core\Attribute\AsEventListener;
    use TYPO3\CMS\Core\Database\ConnectionPool;
    use TYPO3\CMS\Redirects\Event\RedirectIntegrityCheckEvent;
    use TYPO3\CMS\Redirects\Utility\RedirectConflict;

    final readonly class ValidateRedirectTarget
    {
        public function __construct(
            private ConnectionPool $connectionPool,
        ) {}

        #[AsEventListener('my-extension/validate-redirect-target')]
        public function __invoke(RedirectIntegrityCheckEvent $event): void
        {
            $target = $event->getTarget();
            if (!str_starts_with($target, 't3://record')) {
                return;
            }
            // Parse t3://record?identifier=tx_news&uid=456
            parse_str((string)parse_url($target, PHP_URL_QUERY), $params);
            $table = $params['identifier'] ?? '';
            $uid = (int)($params['uid'] ?? 0);
            if ($table === '' || $uid === 0) {
                $event->setIntegrityStatus(RedirectConflict::INVALID_TARGET);
                return;
            }
            $count = $this->connectionPool
                ->getConnectionForTable($table)
                ->count('uid', $table, ['uid' => $uid]);
            if ($count === 0) {
                $event->setIntegrityStatus(RedirectConflict::INVALID_TARGET);
                return;
            }
            // Set to NO_CONFLICT - will not be reported as conflicting redirect
            // but will clear out already other integrity status.
            $event->setIntegrityStatus(RedirectConflict::NO_CONFLICT);
        }
    }

Impact
======

Extensions can now validate redirects during the integrity check by listening
to this event. Broken or invalid redirects are reported alongside existing
self-reference conflicts in the :bash:`redirects:checkintegrity` command
output.

.. index:: CLI, PHP-API, ext:redirects
