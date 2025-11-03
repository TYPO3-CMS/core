..  include:: /Includes.rst.txt

..  _deprecation-109286-1773844395:

================================================================
Deprecation: #109286 - Explicit request handling in PageRenderer
================================================================

See :issue:`109286`

Description
===========

Some methods of class :php:`TYPO3\CMS\Core\Page\PageRenderer` require an instance of
:php:`ServerRequestInterface` being hand over since TYPO3 v14.2:

setLanguage()
-------------

* Old: :php:`PageRenderer->setLanguage(Locale $locale, ?ServerRequestInterface $request = null)`
* TYPO3 v14.2: :php:`PageRenderer->setLanguage(Locale $locale, ?ServerRequestInterface $request = null)`
* TYPO3 v15: :php:`PageRenderer->setLanguage(Locale $locale, ServerRequestInterface $request)`

setDocType()
------------

* Old: :php:`PageRenderer->setDocType(DocType $docType)`
* TYPO3 v14.2: :php:`PageRenderer->setDocType(DocType $docType, ?ServerRequestInterface $request = null)`
* TYPO3 v15: :php:`PageRenderer->setDocType(DocType $docType, ServerRequestInterface $request)`

render()
--------

* Old: :php:`PageRenderer->render()`
* TYPO3 v14.2: :php:`PageRenderer->render(?ServerRequestInterface $request = null)`
* TYPO3 v15: :php:`PageRenderer->render(ServerRequestInterface $request)`

renderResponse()
----------------

* Old: :php:`PageRenderer->renderResponse(int $code = 200, string $reasonPhrase = '')`
* TYPO3 v14.2: :php:`PageRenderer->render(ServerRequestInterface|int $requestOrCode = 200, int|string $codeOrReasonPhrase = '', string $reasonPhrase = '')`
* TYPO3 v15: :php:`PageRenderer->render(ServerRequestInterface $request, int $code = 200, string $reasonPhrase = '')`


Impact
======

Request dependencies within :php:`PageRenderer` are no longer implicit by accessing :php:`$GLOBALS['TYPO3_REQUEST']`
but need to be hand over explicitly. Not handing over Request to above methods will trigger a deprecation level log
message with TYPO3 v14 and will trigger a fatal PHP error with TYPO3 v15.


Affected installations
======================

The PageRenderer is a low level core class. Many extensions use higher level API and are not affected by the
change directly.


Migration
=========

Adapt the method calls to hand over the Request object.

..  index:: Backend, Frontend, PHP-API, NotScanned, ext:core
