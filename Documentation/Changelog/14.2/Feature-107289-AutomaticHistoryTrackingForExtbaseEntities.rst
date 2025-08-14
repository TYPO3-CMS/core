.. include:: /Includes.rst.txt

.. _feature-107289-1734172800:

==================================================================
Feature: #107289 - Automatic history tracking for Extbase entities
==================================================================

See :issue:`107289`

Description
===========

TYPO3 now automatically tracks history for all Extbase domain entities by
listening to Extbase persistence events and storing them in the :sql:`sys_history`
table. This provides a comprehensive audit trail for all frontend and backend
operations on Extbase entities without requiring any code changes.

The feature leverages TYPO3's existing :php:`RecordHistoryStore` infrastructure
and integrates seamlessly with the backend's record history functionality.

The history tracking captures:

- **Create operations** when entities are persisted for the first time
- **Update operations** when existing entities are modified
- **Delete operations** when entities are removed from persistence

All operations are tracked with proper user context (frontend users, backend users,
or anonymous operations) and include full entity data snapshots.

Configuration
=============

History tracking is **disabled** by default. It can be enabled with the feature toggle
`extbase.enableHistoryTracking` (available via :guilabel:`System > Settings > Feature toggles`).

Once the feature toggle is enabled, history tracking will be enabled for all
extbase domain model storage tables. It can then be **disabled** via TCA on a per-table
basis:

..  code-block:: php
    :emphasize-lines: 11-13
    :caption: EXT:my_extension/Configuration/TCA/tx_myextension_domain_model_blog.php

    <?php
    declare(strict_types=1);
    return [
        'ctrl' => [
            'title' => 'my_extension.messages:my_title',
            'label' => 'uid',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'delete' => 'deleted',
            // ...
            'extbase' => [
                'enableHistoryTracking' => false
            ],
        ],
        'columns' => [
            // ...
        ],
    ];

Defining this on a TCA level (instead of TypoScript `persistence` configuration) has
the advantage, that it can be configured on a table level, and also easily be
evaluated in any context (Backend, Frontend, CLI).

If a third-party extension enables history tracking via TCA, it can be disabled
via TCA overrides. As noted, disabling the feature toggle will also disable all history
tracking even for tables configured with `enableHistoryTracking => true`.

Additionally, these PSR-14 event listeners can be de-registered
or replaced on instance-level:

*   `extbase-history-tracker-persisted`
*   `extbase-history-tracker-updated`
*   `extbase-history-tracker-removed`

..  note::

    Be aware that enabling history tracking can create many history entries
    for extbase entities. They will also be mixed with regular editorial
    changes to extbase entities performed in the TYPO3 backend (FormEngine).

..  important::

    All differences to data in extbase entities are now logged, and the initial
    logging will contain all properties. Take note that this can affect
    GDPR / DSGVO / security-related data storage precautions, and data might need to be
    regularly pruned. It might be advisable to turn off history tracking for
    these tables with "private" data. Due to this reason, the feature toggle
    is set to "false" by default, so an instance-wide opt-in for this is required.

Impact
======

It is now possible that all Extbase domain entities can now
automatically have their changes tracked in the :sql:`sys_history` table,
making them visible in the backend's record history functionality. For this,
the new feature toggle `extbase.enableHistoryTracking` must be enabled
(defaults to `false`).

This feature provides administrators and developers with full
visibility into data changes without requiring any interface implementations
or code modifications.

Technical Details
=================

The implementation consists of a PSR-14 event listener
:php:`TYPO3\CMS\Extbase\EventListener\ExtbaseHistoryTracker` that automatically
registers for the following Extbase persistence events:

- :php:`TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent`
- :php:`TYPO3\CMS\Extbase\Event\Persistence\EntityUpdatedInPersistenceEvent`
- :php:`TYPO3\CMS\Extbase\Event\Persistence\EntityRemovedFromPersistenceEvent`

All entities with valid TCA configuration will be tracked automatically.
This utilizes the Extbase DataMap API, TCA Schema API and RecordHistoryStore API.

.. index:: PHP-API, ext:extbase
