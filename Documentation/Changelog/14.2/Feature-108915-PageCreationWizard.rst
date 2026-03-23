..  include:: /Includes.rst.txt

..  _feature-108915-1742484720:

===========================================
Feature: #108915 - New Page Creation Wizard
===========================================

See :issue:`108915`

Description
===========

The TYPO3 Backend now features a new, guided "Page Creation Wizard" designed to
streamline the page creation process. This interface replaces the traditional,
technically complex workflow with a modular and accessible step-by-step process.

The primary goals of the wizard are to ensure data integrity by enforcing mandatory
fields during creation, improve accessibility, and provide  a modern, responsive
user experience that requires no deep TYPO3-specific expertise.

Key Features
------------

*   **Guided Workflow:** A step-by-step process including positioning, type selection,
    data entry, and a final review before persistence.
*   **Data Integrity:** Validation of required fields (e.g., page title)
    occurs at each step to prevent "broken" or incomplete page records.
*   **Context-Aware:** The wizard can be triggered from various entry points (e.g.,
    page tree, Records module) and respects pre-defined parameters like position
    or page type.
*   **Modular and Extensible:** Built using a generic architecture that allows
    integrators to add custom steps or extend existing configurations for specific
    page types.
*   **FormEngine Integration:** Dynamic steps are rendered using FormEngine,
    ensuring that all TCA-based rules and field configurations are respected.
*   **Post-Creation Actions:** After successful creation, users can choose to jump
    directly to the Layout module, create another page, or return to their previous task.

Impact
======

Editors benefit from a faster, less error-prone way to build page structures.
The intuitive interface significantly lowers the barrier to entry for new users
while maintaining the flexibility required by power users.

Developers and integrators can leverage the modular design to customize the
creation process for custom `doktypes` or even adapt the wizard concept for
other TYPO3 workflows in the future.

..  index:: Backend, ext:backend
