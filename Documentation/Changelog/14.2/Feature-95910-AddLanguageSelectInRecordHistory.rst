..  include:: /Includes.rst.txt

..  _feature-95910-1754604644:

=========================================================================
Feature: #95910 - Add a language selector in the record history/undo view
=========================================================================

See :issue:`95910`

Description
===========

The backend view for listing the history / audit of a record and undo / rollback
functionality is enhanced by adding a language selection to give editors the
ability to switch between a records' translations.

The new language selection dropdown is only shown if the record is language
aware. The available languages are determined by the translations and the user's
`allowed_languages` (given by their groups).

Impact
======

The history/undo view of a translated record can now be reached from the page
tree and other places where a context menu is available, not just the list
module and page module language comparison view.

..  index:: Backend, ext:backend
