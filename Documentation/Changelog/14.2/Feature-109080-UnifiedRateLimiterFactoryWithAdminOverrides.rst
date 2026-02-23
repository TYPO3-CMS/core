..  include:: /Includes.rst.txt

..  _feature-109080-1740000001:

==================================================================
Feature: #109080 - Unified RateLimiterFactory with admin overrides
==================================================================

See :issue:`109080`

Description
===========

TYPO3's :php:`\TYPO3\CMS\Core\RateLimiter\RateLimiterFactory` has been
refactored to serve as the single entry point for all rate limiting across the
system. A new :php:`\TYPO3\CMS\Core\RateLimiter\RateLimiterFactoryInterface`
extends Symfony's :php:`RateLimiterFactoryInterface` with additional convenience
methods for request-based and login rate limiting.

Previously, the backend and frontend password recovery features, as well as the
Extbase rate limiting, each created Symfony rate limiter factories directly,
bypassing TYPO3's factory. All consumers now use the central TYPO3 factory,
which enables a unified admin override mechanism.

Extension developers should type-hint against
:php:`\TYPO3\CMS\Core\RateLimiter\RateLimiterFactoryInterface` when injecting
the factory.

Admin overrides via TYPO3_CONF_VARS
------------------------------------

A new configuration option :php:`$GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']`
allows administrators to override any rate limiter's settings by its ID. Each key
is a limiter ID, and each value is an array of settings to override:

..  code-block:: php
    :caption: config/system/additional.php

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']['login-be'] = [
        'limit' => 3,
        'interval' => '5 minutes',
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']['backend-password-recovery'] = [
        'limit' => 1,
        'interval' => '1 hour',
    ];

Known limiter IDs:

*   :php:`login-be` — Backend login
*   :php:`login-fe` — Frontend login
*   :php:`backend-password-recovery` — Backend password reset
*   :php:`felogin-password-recovery` — Frontend password recovery
*   :php:`extbase-<classSlug>-<actionMethod>` — Extbase :php:`#[RateLimit]` actions

Example limiter ID for Extbase action
-------------------------------------

The limiter ID for an Extbase action which uses the :php:`#[RateLimit]`
attribute is constructed using the "slugified" classname and the
action method name.

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/MyController.php

    namespace Vendor\MyExtension\Controller;

    use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
    use TYPO3\CMS\Extbase\Attribute\RateLimit;

    class MyController extends ActionController
    {
        #[RateLimit(limit: 5, interval: '10 minutes', message: 'ratelimit.dosomething')]
        public function doSomethingAction(): ResponseInterface
        {
            return $this->redirect('index');
        }
    }

The limiter ID for the action is:
:php:`extbase-vendor-myextension-controller-mycontroller-dosomethingaction`

General-purpose rate limiting
-----------------------------

Extension developers can now use the factory for custom rate limiting needs.

The :php:`createRequestBasedLimiter()` method is the recommended entry point for
request-scoped rate limiting. It automatically extracts the client's remote IP
from the PSR-7 request and uses it as the limiter key:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Service/MyService.php

    use Psr\Http\Message\ServerRequestInterface;
    use TYPO3\CMS\Core\RateLimiter\RateLimiterFactoryInterface;

    class MyService
    {
        public function __construct(
            private readonly RateLimiterFactoryInterface $rateLimiterFactory,
        ) {}

        public function doSomething(ServerRequestInterface $request): void
        {
            $limiter = $this->rateLimiterFactory->createRequestBasedLimiter(
                $request,
                [
                    'id' => 'my-extension-action',
                    'policy' => 'sliding_window',
                    'limit' => 10,
                    'interval' => '1 hour',
                ]
            );

            $limit = $limiter->consume();
            if (!$limit->isAccepted()) {
                // handle rate limit exceeded
            }
        }
    }

For cases where a custom key is needed (e.g. a user ID instead of the IP), the
:php:`createLimiter()` method accepts an explicit configuration array and key:

..  code-block:: php

    $limiter = $this->rateLimiterFactory->createLimiter(
        [
            'id' => 'my-extension-action',
            'policy' => 'sliding_window',
            'limit' => 10,
            'interval' => '1 hour',
        ],
        $userId
    );

Pre-configured named services can also be defined in :file:`Services.yaml`,
which are then injectable with the :php:`create()` method from the
:php:`RateLimiterFactoryInterface`:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    myRateLimiter:
      class: TYPO3\CMS\Core\RateLimiter\RateLimiterFactory
      arguments:
        $config:
          id: 'my-custom-limiter'
          policy: 'sliding_window'
          limit: 5
          interval: '10 minutes'


Impact
======

All rate limiting in TYPO3 now flows through a single factory that supports
admin-level overrides. Administrators can tune or restrict rate limits for any
component — login, password recovery, Extbase actions, or custom extensions —
without modifying code, using :php:`$GLOBALS['TYPO3_CONF_VARS']['SYS']['rateLimiter']`.

The login rate limiter now uses human-readable IDs (:php:`login-be`,
:php:`login-fe`) instead of SHA1 hashes. Existing cached rate limit state
from previous versions will expire naturally.

..  index:: PHP-API, LocalConfiguration, ext:core
