..  include:: /Includes.rst.txt

..  _important-93765-1738100000:

===========================================================
Important: #93765 - Extbase identity map now language-aware
===========================================================

See :issue:`93765`

Description
===========

The Extbase persistence session's identity map now includes language context
when caching domain objects. Previously, the identity map used identifiers
based only on the record's UID and localized UID, which could cause incorrect
translations to be returned when the same object was accessed with different
:php:`LanguageAspect` configurations within the same request.

The identity map identifier now includes the language context,
specifically the :php:`contentId`, :php:`overlayType`, and :php:`fallbackChain`
properties of the :php:`LanguageAspect`.

Impact
======

This change ensures that objects loaded with different language configurations
are cached separately in the identity map. For example, if an object is first
loaded with :php:`OVERLAYS_ON` and then queried again with :php:`OVERLAYS_MIXED`,
the system will correctly return different cached objects for each context.

The change is transparent for most use cases. However, objects retrieved with
different language settings are now distinct instances. Code relying on
object identity (e.g., using `===` comparison) between objects loaded with
different language settings will need adjustment.

.. index:: PHP-API, ext:extbase
