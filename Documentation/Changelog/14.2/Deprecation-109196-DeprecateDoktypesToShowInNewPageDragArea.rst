..  include:: /Includes.rst.txt

..  _deprecation-109196-1742122800:

==============================================================================
Deprecation: #109196 - Deprecate doktypesToShowInNewPageDragArea User TSConfig
==============================================================================

See :issue:`109196`

Description
===========

The User TSConfig option :tsconfig:`options.pageTree.doktypesToShowInNewPageDragArea`
has been marked as deprecated and will be removed in TYPO3 v15.0.

The page tree toolbar submenu now automatically determines available doktypes
based on the users group permissions. The manual TSConfig configuration is no
longer needed.

Impact
======

Using the deprecated User TSConfig option triggers a deprecation-level log
entry and will stop working in TYPO3 v15.0.

Affected installations
======================

TYPO3 installations that set
:tsconfig:`options.pageTree.doktypesToShowInNewPageDragArea` in their User
TSConfig are affected.

Migration
=========

Remove the :tsconfig:`options.pageTree.doktypesToShowInNewPageDragArea` option
from your User TSConfig. The page tree toolbar now automatically shows all
doktypes the current backend user is allowed to create based on their group
permissions.

..  index:: Backend, TSConfig, NotScanned, ext:backend
