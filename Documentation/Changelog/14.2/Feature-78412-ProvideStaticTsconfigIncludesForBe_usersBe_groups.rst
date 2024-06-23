.. include:: /Includes.rst.txt

.. _feature-78412-1719144405:

===========================================================================
Feature: #78412 - Provide static tsconfig includes for be_users & be_groups
===========================================================================

See :issue:`78412`

Description
===========

The tables `be_users` and `be_groups` are extended by an additional field which
allows to select static Tsconfig defined by extensions. Following the syntax for
the field `tsconfig_includes` in the table `pages` there are the following
methods available:

For Backend users:

.. code-block:: php

    <?php

    // in Extension/Configuration/TCA/Overrides/be_users.php
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerUserTSConfigFile(
        'extensionKey',
        'Configuration/Tsconfig/Static/example1.tsconfig',
        'Example 1'
    );

For Backend usergroups:

.. code-block:: php

    <?php

    // in Extension/Configuration/TCA/Overrides/be_groups.php
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerUserGroupTSConfigFile(
        'extensionKey',
        'Configuration/Tsconfig/Static/example2.tsconfig',
        'Example 2'
    );

Impact
======

The new fields can be used to define User Tsconfig code for specific users and
usergroups which can be provided by extensions.

By using this approach instead of writing TSconfig directly to the TSconfig database field of
`be_users` or `be_groups` reduces the amount of configuration saved in the database. This has
many advantages in following scenarios:

- Running a TYPO3 instance with automated deployment and GIT version control makes it easily
  possible to create/modify such an includable TSconfig snippet via a file change. It also helps
  keeping configuration streamlined for multiple environments like staging or production. Once
  the file is included you have the same user/group configuration without manually changing that
  in the database for each affected user or group.

- Extension authors can ship predefined User TSconfig files which can be included by the TYPO3
  backend user. That also applies to local (site) packages or your agency's base package.

- Possible breaking changes in major upgrades can be automatically upgraded with tools like
  TYPO3 Fractor (enhancement for TYPO3 Rector). If a breaking change occurs within User TSconfig,
  such a tool can automatically upgrade the configuration ensuring that you don't forget to
  search for them in the depths of the database. This kind of approach could reduce the amount
  of recurring manual work. Particularly affected by this are large TYPO3 instances with a big
  amount of `be_users` or `be_groups` records.

- Searching within existing User TSconfig which is stored in the database otherwise is possible
  in your IDE now. All your TSconfig is present in your codebase. This can also improve your
  daily productivity and greatly simplifies major upgrades. You can also go further and disable/
  hide the `TSconfig` database field in projects to prevent saving User TSconfig in the database
  for users/groups at all.

.. index:: Backend, TSConfig, ext:core
