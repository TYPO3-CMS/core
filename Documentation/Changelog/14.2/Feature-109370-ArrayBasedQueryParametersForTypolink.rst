..  include:: /Includes.rst.txt

..  _feature-109370-1742911200:

=============================================================
Feature: #109370 - Array-based queryParameters for Page Links
=============================================================

See :issue:`109370`

Description
===========

When creating page links programmatically via the :php:`LinkFactory` PHP API,
query parameters previously had to be provided as a URL-encoded string using
the :php:`additionalParams` configuration key. A new :php:`queryParameters`
configuration key has been introduced that accepts a PHP array, including
multi-dimensional arrays.

When both :php:`queryParameters` and :php:`additionalParams` are set, they are
merged using :php:`array_replace_recursive()`, with :php:`queryParameters`
taking precedence.

Example:

..  code-block:: php

    $linkFactory->create('Link text', [
        'parameter' => 42,
        'queryParameters' => [
            'tx_news' => [
                'action' => 'show',
                'id' => 123,
            ],
        ],
    ], $contentObjectRenderer);

The Fluid ViewHelpers :html:`<f:link.page>`, :html:`<f:uri.page>`,
:html:`<f:link.action>` and :html:`<f:uri.action>` now use this option
internally to pass their :html:`additionalParams` argument directly as an
array, eliminating a previous serialize/deserialize roundtrip via query
string encoding.

Impact
======

Developers creating page links via the :php:`LinkFactory` PHP API can now pass
query parameters as structured arrays via the :php:`queryParameters`
configuration key. This avoids manual query string encoding and makes
multi-dimensional parameter handling more natural.

The option can be combined with the existing string-based
:php:`additionalParams`. When both are provided, :php:`queryParameters` values
override matching keys from :php:`additionalParams`.

..  index:: Frontend, PHP-API, ext:frontend
