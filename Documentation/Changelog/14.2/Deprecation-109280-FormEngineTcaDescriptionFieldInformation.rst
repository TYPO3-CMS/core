..  include:: /Includes.rst.txt

..  _deprecation-109280-1742109280:

=================================================================
Deprecation: #109280 - FormEngine TcaDescription fieldInformation
=================================================================

See :issue:`109280`

Description
===========

The :php:`TcaDescription` field information render type has been deprecated.
Field descriptions configured via TCA :php:`['columns']['fieldName']['description']`
are now rendered automatically next to the field label by
:php:`AbstractFormElement::renderDescription()` and
:php:`AbstractContainer::renderDescription()`.

Previously, every FormEngine element and container registered
:php:`tcaDescription` as a default field information node, which rendered the
description inside the element body. The description is now rendered directly
after the label or legend element, providing a more consistent placement across
all field types.

Additionally, the :php:`$defaultFieldInformation` property has been removed
from all core FormEngine elements and containers. Custom elements that extend
core elements and rely on :php:`tcaDescription` being present in
:php:`$defaultFieldInformation` are affected as well.


Impact
======

Using the :php:`tcaDescription` render type in a custom :php:`fieldInformation`
configuration will trigger a PHP :php:`E_USER_DEPRECATED` level error. The
render type still exists but returns empty output during the deprecation period,
since descriptions are now rendered at the label level.

Custom FormEngine nodes that extend core elements and override
:php:`$defaultFieldInformation` to include :php:`tcaDescription` will still
work, but the :php:`tcaDescription` entry will trigger the deprecation warning.


Affected installations
======================

*  Installations with extensions that explicitly configure :php:`tcaDescription`
   as a field information node in TCA:

   ..  code-block:: php

       'fieldInformation' => [
           'tcaDescription' => [
               'renderType' => 'tcaDescription',
           ],
       ],

*  Custom FormEngine elements or containers that set :php:`tcaDescription` in
   their :php:`$defaultFieldInformation` property:

   ..  code-block:: php

       protected $defaultFieldInformation = [
           'tcaDescription' => [
               'renderType' => 'tcaDescription',
           ],
       ];

Extensions that only use the standard TCA :php:`description` property are not
affected — descriptions will continue to be rendered automatically.


Migration
=========

Remove any explicit :php:`tcaDescription` field information configuration from
TCA and from custom FormEngine node classes. Field descriptions are now rendered
automatically next to the label and no longer require a field information node.

**TCA configuration**

Before:

..  code-block:: php

    'columns' => [
        'my_field' => [
            'label' => 'My field',
            'description' => 'Help text for this field',
            'config' => [
                'type' => 'input',
                'fieldInformation' => [
                    'tcaDescription' => [
                        'renderType' => 'tcaDescription',
                    ],
                ],
            ],
        ],
    ],

After:

..  code-block:: php

    'columns' => [
        'my_field' => [
            'label' => 'My field',
            'description' => 'Help text for this field',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],

**Custom FormEngine nodes with defaultFieldInformation**

If your custom element only had :php:`tcaDescription` in
:php:`$defaultFieldInformation`, remove the property entirely:

Before:

..  code-block:: php

    class MyCustomElement extends AbstractFormElement
    {
        protected $defaultFieldInformation = [
            'tcaDescription' => [
                'renderType' => 'tcaDescription',
            ],
        ];
    }

After:

..  code-block:: php

    class MyCustomElement extends AbstractFormElement
    {
        // tcaDescription is no longer needed, descriptions are
        // rendered automatically next to the label.
    }

If your custom element has other field information entries alongside
:php:`tcaDescription`, only remove the :php:`tcaDescription` entry:

Before:

..  code-block:: php

    class MyCustomElement extends AbstractFormElement
    {
        protected $defaultFieldInformation = [
            'tcaDescription' => [
                'renderType' => 'tcaDescription',
            ],
            'myCustomInfo' => [
                'renderType' => 'myCustomInfo',
            ],
        ];
    }

After:

..  code-block:: php

    class MyCustomElement extends AbstractFormElement
    {
        protected $defaultFieldInformation = [
            'myCustomInfo' => [
                'renderType' => 'myCustomInfo',
            ],
        ];
    }

..  index:: Backend, PHP-API, TCA, NotScanned, ext:backend
