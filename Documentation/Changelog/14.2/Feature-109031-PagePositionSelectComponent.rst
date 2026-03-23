.. include:: /Includes.rst.txt

.. _feature-109031:

=================================================
Feature: #109031 - Page Position Select Component
=================================================

See :issue:`109031`

Description
===========

A new component has been added based on the `page-browser` that allows
selecting a page and defining the insertion position. The available
positions are `inside` and `after`.

Features
--------

- When a page node in the tree is selected, insertion options are displayed.
  Options include `Insert` and `After`, with `After` applicable to all direct
  child pages.

- On first render, the selected node is automatically scrolled into view
  using `scrollNodeIntoViewIfNeeded`.

- The component emits a Custom Event
  `typo3:page-position-select-tree:insert-position-change` whenever the
  insertion position changes. The event payload contains:
  `pageUid` (the selected page ID) and `position` (the chosen insertion
  position), allowing other modules to react accordingly.

Example usage
=============

..  code-block:: html
     <typo3-backend-component-page-position-select
      activePageId="1"
      insertPosition="inside"
    >
    </typo3-backend-component-page-position-select>

Impact
======

This component can be used anywhere in the backend where page selection and
insertion position specification are needed, replacing previous workflows
with more intuitive controls.

.. index:: Backend
