..  include:: /Includes.rst.txt

..  _feature-109366-1742900000:

===========================================================
Feature: #109366 - Hide form fields with no selectable items
===========================================================

See :issue:`109366`

Description
===========

Relational fields in FormEngine, such as :php:`type=select` with
:php:`foreign_table`, :php:`type=category`, or :php:`type=select` with
misconfigured empty items are now automatically handled when no selectable
items are available. Additionally, :php:`type=language` fields are hidden when
only a single language is configured, since a dropdown with one choice serves
no purpose.

For regular users, the field is removed entirely. When backend debug mode is
enabled (:php:`$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = true`), the field
is kept as read-only with an info badge, so admins can identify configuration
issues such as missing records or restrictive TSconfig.

Common scenarios where this applies:

*   A :php:`categories` field is displayed but no :sql:`sys_category` records exist.
*   A select field references :sql:`fe_groups` but no frontend user groups have been created.
*   All available items have been removed via TSconfig :typoscript:`removeItems` or
    :typoscript:`keepItems` configuration.
*   The backend user has no permissions for any of the available items.
*   A site has only one language configured.
*   A :php:`type=select` field is misconfigured with no items at all.

Affected field types
--------------------

*   :php:`type=select` (all render types including :php:`selectTree`): hidden when no
    items available. For fields with :php:`foreign_table`, static items like "Hide at
    login" are not counted — they only make sense when actual foreign records exist.
*   :php:`type=category`: hidden when no items available
*   :php:`type=language`: hidden when only one language is available (the special
    :php:`-1` "All languages" item is not counted as a meaningful choice)

Existing values
---------------

Fields not rendered in the form are simply not submitted on save. DataHandler
preserves the existing database value for non-submitted fields, so no data is
lost when a field is hidden by this feature.

Opt-out
-------

The behavior can be disabled per field using the TCA configuration option
:php:`showIfEmpty`:

..  code-block:: php
    :caption: EXT:my_extension/Configuration/TCA/Overrides/tt_content.php

    $GLOBALS['TCA']['tt_content']['columns']['categories']['config']['showIfEmpty'] = true;

Impact
======

Fields without selectable items are now hidden by default. In backend debug
mode, the fields are shown as read-only with an info badge instead. Extensions that rely on always showing empty
fields should set :php:`showIfEmpty` to :php:`true` in their TCA configuration.

..  index:: Backend, TCA
