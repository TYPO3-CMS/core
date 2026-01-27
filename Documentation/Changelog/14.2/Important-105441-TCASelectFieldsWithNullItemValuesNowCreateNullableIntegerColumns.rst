..  include:: /Includes.rst.txt

..  _important-105441-1737992400:

====================================================================================
Important: #105441 - TCA select fields with null item values create nullable columns
====================================================================================

See :issue:`105441`

Description
===========

When configuring a TCA `select` field with `renderType => 'selectSingle'`
and adding an item with `'value' => null`, the generated database column is
now nullable regardless of whether the other item values are integers or strings.

Previously, the following configuration with integer item values would incorrectly
generate a :sql:`VARCHAR(255)` column:

.. code-block:: php

    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'some_table',
        'default' => null,
        'items' => [
            ['label' => 'Please choose', 'value' => null],
        ],
    ],

This now correctly generates :sql:`INT UNSIGNED DEFAULT NULL`.

Similarly, a configuration with string item values now also generates a
nullable column:

.. code-block:: php

    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'default' => null,
        'items' => [
            ['label' => 'Default', 'value' => null],
            ['label' => 'Option', 'value' => 'some_value'],
        ],
    ],

This now correctly generates :sql:`VARCHAR(255) DEFAULT NULL` instead of
:sql:`VARCHAR(255) DEFAULT '' NOT NULL`.

..  index:: Database, TCA, ext:core
