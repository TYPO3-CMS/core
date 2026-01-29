..  include:: /Includes.rst.txt

..  _feature-109018-1769714898:

====================================================================
Feature: #109018 - PSR-14 event to modify indexed_search result sets
====================================================================

See :issue:`109018`

Description
===========

A new PSR-14 event
:php:`\TYPO3\CMS\IndexedSearch\Event\AfterSearchResultSetsAreGeneratedEvent`
has been introduced to modify complete search result sets in
:php:`\TYPO3\CMS\IndexedSearch\Controller\SearchController`.

The event is dispatched in :php:`searchAction()` after all result sets have
been built. Event listeners can manipulate complete result sets, including
pagination, rows, section data, and category metadata.

The event provides the following methods:

*   :php:`getResultSets()`: Returns all result sets of the current search.
*   :php:`setResultSets(array $resultSets)`: Replaces the result sets.
*   :php:`getSearchData()`: Returns the search configuration array.
*   :php:`getSearchWords()`: Returns the array of search words.
*   :php:`getView()`: Returns the view instance.
*   :php:`getRequest()`: Returns the current server request.

Example
=======

The following example replaces every result set pagination with
:php:`SlidingWindowPagination`:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/ModifySearchPaginationListener.php

    <?php

    declare(strict_types=1);

    namespace MyVendor\MyExtension\EventListener;

    use TYPO3\CMS\Core\Attribute\AsEventListener;
    use TYPO3\CMS\Core\Pagination\SimplePagination;
    use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
    use TYPO3\CMS\IndexedSearch\Event\AfterSearchResultSetsAreGeneratedEvent;

    #[AsEventListener(identifier: 'my-extension/modify-search-result-sets')]
    final readonly class ModifySearchPaginationListener
    {
        public function __invoke(AfterSearchResultSetsAreGeneratedEvent $event): void
        {
            $resultSets = $event->getResultSets();
            foreach ($resultSets as $key => $resultSet) {
                if (($resultSet['pagination'] ?? null) instanceof SimplePagination) {
                    $resultSets[$key]['pagination'] = new SlidingWindowPagination(
                        $resultSet['pagination']->getPaginator(),
                        5
                    );
                }
            }

            $event->setResultSets($resultSets);
        }
    }

Impact
======

This event allows modifying complete search result sets in a single listener
call. It enables custom pagination strategies as well as advanced search
result transformations.

..  index:: Frontend, PHP-API, ext:indexed_search
