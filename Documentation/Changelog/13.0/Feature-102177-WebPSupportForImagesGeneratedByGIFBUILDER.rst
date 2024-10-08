.. include:: /Includes.rst.txt

.. _feature-102177-1697483829:

==================================================================
Feature: #102177 - WebP support for images generated by GIFBUILDER
==================================================================

See :issue:`102177`

Description
===========

GIFBUILDER, the image manipulation library for TypoScript based on GDlib, a PHP
extension bundled into PHP, now also supports generating resulting files of
type "webp".

WebP is an image format, that is supported by all modern browsers, and usually
has a better compression (= smaller file size) than jpg files.


Impact
======

If defined via format=webp within a GifBuilder setup, the generated files are
now webp instead of png (the default).

It is possible to define the quality of a webp image similar to jpg images
globally via :php:`$TYPO3_CONF_VARS['GFX']['webp_quality']` or via TypoScript's
"quality" property on a per-image basis. Setting the quality to "101" equivalents
to "lossless" compression.

Example
-------

.. code-block:: typoscript

    page.10 = IMAGE
    page.10 {
      file = GIFBUILDER
      file {
        backColor = yellow
        XY = 1024,199
        format = webp
        quality = 44

        10 = IMAGE
        10.offset = 10,10
        10.file = 1:/my-image.jpg
      }
    }

A new test in the Environment module / Install Tool can be used to check if the
bundled GDlib extension of your PHP version supports the WebP image format.

.. index:: FAL, TypoScript, ext:frontend
