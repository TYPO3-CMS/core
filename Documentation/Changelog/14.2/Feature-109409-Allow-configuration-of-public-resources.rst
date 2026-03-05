.. include:: /Includes.rst.txt

.. _feature-109409-1774770383:

===================================================
Feature: #109409 - Allow configuration of resources
===================================================

See :issue:`109409`

Description
===========

Composer-managed TYPO3
----------------------

Until now, extensions could place public resources only in their
`Resources/Public` folder. The folder name used to publish these
extension resources was a non-configurable MD5 hash.

This feature introduces the possibility to configure resources explicitly.
This includes additional public folders or files that are published into
TYPO3's `public/_assets` folder, as well as non-public resource paths.

TYPO3 classic mode
------------------

In TYPO3 classic mode, there is no visible change for extensions
or the `typo3/app` package, since all files in extensions are already
located within the document root. Restricting resources to the default
locations in classic mode therefore mainly follows coding guidelines
and keeps compatibility with Composer mode.

Configuring extensions
----------------------

Extensions can add `Configuration/Resources.php` to configure resources.
This configuration is then added to the following default configuration:

..  code-block:: php
    :caption: EXT:core/Configuration/DefaultPackageResources.php

    <?php

    declare(strict_types=1);

    use TYPO3\CMS\Core\Package\Package;
    use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
    use TYPO3\CMS\Core\Package\Resource\Definition\ResourceDefinition;

    return static function (Package $package) {
        $resourceDefinitions = [
            new ResourceDefinition('Resources/Private'),
        ];
        if (is_dir($package->getPackagePath() . 'Resources/Public')) {
            $resourceDefinitions[] = new PublicResourceDefinition('Resources/Public');
        }
        return $resourceDefinitions;
    };

This means that, when using the system resources API
(:ref:`feature-107537-1759136314`), resource identifiers are only allowed
to reference files or folders in `Resources/Private` and, if it exists,
`Resources/Public`. It also means that, by default, `Resources/Public`
in extensions will be published in the same way as before this change.

Example: publish an additional public folder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

..  code-block:: php
    :caption: EXT:my_extension/Configuration/Resources.php

    <?php

    declare(strict_types=1);

    use TYPO3\CMS\Core\Package\Package;
    use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
    use TYPO3\CMS\Core\Package\Resource\Definition\ResourceDefinition;

    return static function (Package $package) {
        return [
            new PublicResourceDefinition('Build/Public'),
        ];
    };

This will publish the `Build/Public` folder as well. The published folder name
will be a hash unique to this folder.

Example: publish a single file only
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of publishing a whole folder, a single file can be published:

..  code-block:: php
    :caption: EXT:my_extension/Configuration/Resources.php

    <?php

    declare(strict_types=1);

    use TYPO3\CMS\Core\Package\Package;
    use TYPO3\CMS\Core\Package\Resource\Definition\PublicFileDefinition;

    return static function (Package $package) {
        return [
            new PublicFileDefinition(relativePath: 'Build/styles.css'),
            new PublicFileDefinition(
                relativePath: 'Build/components.css',
                publicPrefix: $package->getPackageKey() . '/custom/folder/my-components.css',
            ),
        ];
    };

This publishes the extension file `Build/styles.css` to a folder with a unique
hash, which will then contain the file `styles.css`.
Additionally, `Build/components.css` will be published to
`_assets/my_extension/custom/folder/my-components.css`.

Example: use a fixed prefix in `_assets`
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, TYPO3 generates the public prefix automatically.
A fixed prefix can be configured explicitly:

..  code-block:: php
    :caption: EXT:my_extension/Configuration/Resources.php

    <?php

    declare(strict_types=1);

    use TYPO3\CMS\Core\Package\Package;
    use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
    use TYPO3\CMS\Core\Package\Resource\Definition\ResourceDefinition;

    return static function (Package $package) {
        return [
            new PublicResourceDefinition(
                relativePath: 'Build/Public',
                publicPrefix: 'my-vendor/my-extension-build',
            ),
        ];
    };

This publishes resources from `Build/Public` to a stable location below
`public/_assets/my-vendor/my-extension-build`.

.. note::

    Resource definitions configured in this file amend the default
    configuration. They are added to the default configuration.

.. important::

    Static public prefixes must be unique across all packages. Reusing the same
    public prefix in multiple packages will cause an exception.

Configuring the typo3/app package
---------------------------------

See the system resources API (:ref:`feature-107537-1759136314`) for more
information about what the `typo3/app` package represents.

To configure the `typo3/app` package, a `config/system/resources.php`
file can be added.

If it is missing, the following default configuration is used:

..  code-block:: php
    :caption: EXT:core/Configuration/DefaultAppResources.php

    <?php

    declare(strict_types=1);

    use TYPO3\CMS\Core\Package\Resource\Definition\PublicResourceDefinition;
    use TYPO3\CMS\Core\Package\VirtualAppPackage;

    return static function (VirtualAppPackage $package, string $relativePublicPath) {
        return [
            new PublicResourceDefinition(
                relativePath: $relativePublicPath . '_assets',
            ),
            new PublicResourceDefinition(
                relativePath: $relativePublicPath . 'uploads',
            ),
            new PublicResourceDefinition(
                relativePath: $relativePublicPath . 'typo3temp/assets',
            ),
        ];
    };

.. note::

    Resource definitions configured in this file amend the default
    configuration. They are added to the default configuration.

.. note::

    It is also recommended not to place additional files directly into the
    `_assets` folder anymore. Instead, configure a folder **outside** the
    document root and let TYPO3 publish it automatically into `_assets`.

    This is important so that third-party publishers can pick up the
    publishing process and actually publish files to the intended location,
    for example a CDN, instead of leaving them in the `_assets` folder.
    If a resource definition configures a source folder that is already
    within the system's public folder, publishing is skipped.

Closing notes
-------------

For now, this change mainly affects public files and folders.

.. important::

    Only basic PHP operations are allowed in this file.
    TYPO3 is not bootstrapped in Composer mode when this file is evaluated.
    This means that, apart from classes being autoloadable, no global state
    is available. Think of this file as a plain PHP file that is executed
    directly as an entry point. Access to files **within** the package folder
    handed over as an object is allowed and intentional.

.. important::

    Relative paths must not contain leading or trailing slashes, backpaths
    such as `../`, or other invalid characters.

.. note::

    Changes to the resource configuration require execution of
    `composer dumpautoload` or, in TYPO3 classic mode, `cache:flush`.

Impact
======

If no resource configuration is added to extensions, this feature has
no impact on a TYPO3 installation. If such a configuration exists,
it extends the default configuration.

.. index:: ext:core
