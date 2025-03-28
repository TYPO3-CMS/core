.. include:: /Includes.rst.txt

.. _feature-102159-1675976883:

=============================================================================
Feature: #102159 - Support additional parameters for TCA slug prefix userFunc
=============================================================================

See :issue:`102159`

Description
===========

TCA slug prefix user functions now receive the full TCA field configuration
and the field name as additional parameters.

User Function Implementation
============================

The prefix user function receives two additional keys alongside the existing
parameters:

- :php:`fieldName` - The name of the slug field
- :php:`config` - The full TCA configuration array of the slug field

.. code-block:: php
   :caption: EXT:my_extension/Classes/Utility/SlugUtility.php

    namespace MyExtension\Utility;

    class SlugUtility
    {
        public function generatePrefix(array $parameters): string
        {
            // Standard parameters (always available)
            $site = $parameters['site'];
            $languageId = $parameters['languageId'];
            $table = $parameters['table'];
            $row = $parameters['row'];

            $fieldName = $parameters['fieldName'];
            $config = $parameters['config'];

            return '/default/';
        }
    }

Available Parameters
====================

The user function receives an array with the following keys:

- :php:`site` - The current site object
- :php:`languageId` - The current language ID (int)
- :php:`table` - The table name (string)
- :php:`row` - The current record data (array)
- :php:`fieldName` - The name of the slug field (string)
- :php:`config` - The full TCA configuration array of the slug field

Impact
======

Extension developers can access the complete TCA field configuration and the
field name directly within prefix user functions.

.. index:: Backend, TCA, ext:backend
