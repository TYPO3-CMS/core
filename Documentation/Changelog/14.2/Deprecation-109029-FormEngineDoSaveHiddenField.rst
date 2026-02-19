..  include:: /Includes.rst.txt

..  _deprecation-109029-1771804800:

=========================================================
Deprecation: #109029 - FormEngine ``doSave`` hidden field
=========================================================

See :issue:`109029`

Description
===========

The :html:`<input type="hidden" name="doSave">` field in FormEngine was a
legacy mechanism where JavaScript set the field value to :html:`1` in order to
signal PHP that the submitted form data should be processed as a save operation.

This indirection is no longer needed. TYPO3 now uses native submit button values
such as :html:`_savedok` directly, which are sufficient to determine whether a
save should be performed. The field is **no longer evaluated internally**.

For backwards compatibility the :html:`doSave` field is still appended to the
form on programmatic saves in TYPO3 v14, but this behaviour is deprecated and
will be removed in TYPO3 v15.


Impact
======

Third-party code reading :php:`$request->getParsedBody()['doSave']` to detect
whether a save operation was triggered will stop working in TYPO3 v15.


Affected Installations
======================

Installations with custom backend modules or extensions that inspect the
:html:`doSave` POST field to determine whether incoming form data should be
persisted.


Migration
=========

Replace any check against the :html:`doSave` POST field with a check against
the native submit action fields that FormEngine sends as part of its regular
form submission.

Before:

..  code-block:: php

    $parsedBody = $request->getParsedBody();
    $doSave = (bool)($parsedBody['doSave'] ?? false);
    if ($doSave) {
        // process data
    }

After:

..  code-block:: php

    $parsedBody = $request->getParsedBody();
    $isSaveAction = !empty($parsedBody['_savedok'])
        || !empty($parsedBody['_saveandclosedok'])
        || !empty($parsedBody['_savedokview'])
        || !empty($parsedBody['_savedoknew']);
    if ($isSaveAction) {
        // process data
    }

..  index:: Backend, JavaScript, NotScanned, ext:backend
