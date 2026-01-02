..  include:: /Includes.rst.txt

..  _feature-108653-1767199420:

======================================================
Feature: #108653 - Database storage for form extension
======================================================

See :issue:`108653`

Description
===========

The :composer:`typo3/cms-form` extension has been extended with a new database
storage adapter (:php-short:`\TYPO3\CMS\Form\Storage\DatabaseStorageAdapter`),
allowing form definitions to be stored in the database table :sql:`form_definition`
instead of exclusively relying on file system storage.

Form definitions can now be stored in three different ways:

- **Database storage** (new, recommended) – stored as records in the
  :sql:`form_definition` table
- **File mounts (FAL)** – stored as :file:`.form.yaml` files in FAL storage
  (deprecated, see :ref:`deprecation-108653-1741600000`)
- **Extension paths** – shipped with extensions (read-only or configurable)

Storage adapter architecture
----------------------------

The storage layer uses the Chain of Responsibility pattern. Each storage
adapter implements
:php-short:`\TYPO3\CMS\Form\Storage\StorageAdapterInterface` and declares
which persistence identifiers it can handle via its :php:`supports()` method.
The :php-short:`\TYPO3\CMS\Form\Storage\StorageAdapterFactory` iterates
through all registered adapters sorted by priority and delegates to the first
matching adapter.

Three adapters are shipped:

- :php-short:`\TYPO3\CMS\Form\Storage\DatabaseStorageAdapter` (priority 100)
- :php-short:`\TYPO3\CMS\Form\Storage\ExtensionStorageAdapter` (priority 75)
- :php-short:`\TYPO3\CMS\Form\Storage\FileMountStorageAdapter` (priority 50,
  deprecated)

Database table :sql:`form_definition`
-------------------------------------

A new TCA-managed table :sql:`form_definition` stores the form definitions
with the following fields:

- :sql:`label` – the human-readable form name
- :sql:`identifier` – the unique form identifier (e.g., `contact-form`)
- :sql:`configuration` – the full form definition as JSON

Records are read-only in the standard TCA editing interface. All write and
delete operations go through
:php-short:`\TYPO3\CMS\Core\DataHandling\DataHandler`, ensuring proper
permission checks, history tracking, and hook execution.

Form Manager wizard
-------------------

A new **Storage** wizard step lets editors choose the storage type (file
mount, extension, database) when creating or duplicating forms. When only
one storage adapter is accessible, the step auto-advances.

The Form Manager now also shows a **record history** action in the dropdown menu
for database-stored forms, linking to the TYPO3 record history module.

Record list integration
-----------------------

Two event listeners customize the record list for :sql:`form_definition`
records:

- The standard **edit** action is replaced with a link to the Form Editor
  module.
- The standard **delete** action is removed; deletion is only possible
  through the Form Manager.
- Clicking the **record title** opens the Form Editor instead of the TCA
  editing form.

Direct creation of :sql:`form_definition` records via the "New Record" wizard
is denied through Page TSconfig:

..  code-block:: typoscript

    mod.web_list.deniedNewTables := addToList(form_definition)

CLI command: transfer between storages
--------------------------------------

A new CLI command :bash:`form:formdefinition:transfer` allows transferring form
definitions between any two storage backends. This is particularly useful for
migrating file-based forms to database storage from the command line.

..  code-block:: bash

    # Transfer all forms from file mounts to database
    bin/typo3 form:formdefinition:transfer --source=filemount --target=database

    # Transfer a specific form by its identifier
    bin/typo3 form:formdefinition:transfer --source=extension --target=database --form-identifier=contact

    # Move forms (transfer + delete from source)
    bin/typo3 form:formdefinition:transfer --source=filemount --target=database --move

    # Dry-run: preview what would be transferred without making changes
    bin/typo3 form:formdefinition:transfer --source=filemount --target=database --dry-run

    # Transfer to a specific target location (PID for database storage)
    bin/typo3 form:formdefinition:transfer --source=filemount --target=database --target-location=0

Available options:

- :bash:`--source` – Source storage type (`database`, `extension`,
  `filemount`)
- :bash:`--target` – Target storage type
- :bash:`--target-location` – Target storage location
- :bash:`--form-identifier` – Transfer only a specific form
- :bash:`--move` – Delete the source form after successful transfer
- :bash:`--dry-run` – Preview without making changes

Configuration
=============

Backend users must have table access rights for the :sql:`form_definition`
table.

..  important::

   **Permission model differences between file-based and database storage**

   The file-based storage allows granular access control through TYPO3 file
   mounts: different backend user groups can be restricted to different
   storage folders, effectively isolating which forms each group can see and
   edit.

   The database storage currently relies on TCA table permissions
   (:sql:`tables_select` / :sql:`tables_modify` for :sql:`form_definition`).
   This means that all backend users who have table access can see
   **all** database-stored form definitions — there is no
   equivalent to the file mount–based isolation yet.

   A dedicated access control mechanism (comparable to file mount
   isolation) for database-stored forms is planned but not yet implemented.

   If your installation depends on separate permission boundaries for
   different editor groups, it is recommended to **not migrate** to database
   storage at this time and continue using file-based storage until the
   permission feature is available.

Impact
======

Editors can store new form definitions in the database by selecting the "Database"
storage type in the Form Manager's creation wizard.

The file-based storage (file mounts) remains functional during the deprecation
period but triggers :php:`E_USER_DEPRECATED` errors. See
:ref:`deprecation-108653-1741600000` for migration instructions. Existing
file-based forms are not affected by this change.

The extension-based storage continues to work unchanged.

..  index:: Backend, Database, TCA, ext:form
