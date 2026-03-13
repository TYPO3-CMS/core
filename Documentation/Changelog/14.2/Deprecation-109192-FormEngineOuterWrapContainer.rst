..  include:: /Includes.rst.txt

..  _deprecation-109192-1741560000:

=====================================================
Deprecation: #109192 - FormEngine OuterWrapContainer
=====================================================

See :issue:`109192`

Description
===========

The :php:`OuterWrapContainer` FormEngine container has been deprecated in favor
of the new :php:`FormWrapContainer`. The old container rendered record headers,
type icons and record identity information inside FormEngine, which forced
controllers to hide unwanted elements via CSS hacks.

The new :php:`FormWrapContainer` only handles the form wrapping (description,
read-only notice, field information, field wizards and child HTML). Rendering
record headers and identity information is now the responsibility of the
controllers themselves.


Impact
======

Using the :php:`outerWrapContainer` render type will trigger a PHP
:php:`E_USER_DEPRECATED` level error. The container still works as before
during the deprecation period.


Affected installations
======================

Installations with custom controllers or FormEngine integrations that set
:php:`$formData['renderType'] = 'outerWrapContainer'`.


Migration
=========

Replace the render type :php:`outerWrapContainer` with :php:`formWrapContainer`.

Before:

..  code-block:: php

    $formData['renderType'] = 'outerWrapContainer';
    $formResult = $this->nodeFactory->create($formData)->render();

After:

..  code-block:: php

    $formData['renderType'] = 'formWrapContainer';
    $formResult = $this->nodeFactory->create($formData)->render();

Note that :php:`FormWrapContainer` no longer renders the record heading
(:html:`<h1>`) or the record identity footer (icon, table title, uid). If your
controller relied on these being rendered by :php:`OuterWrapContainer`, you need
to render them in your controller code.

..  index:: Backend, PHP-API, NotScanned, ext:backend
