.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _setup:

Setup
============

This chapter explains how to install and configure the extension

Installation
------------
The preferred way to install this extension is via `Composer <https://getcomposer.org>`__:

.. code-block:: bash

    composer require arndtteunissen/column-layout

Alternatively you can install via the Extension Manager. Just search for the extension key |ext_key| (`Extension Page`_)

.. _Extension Page: https://extensions.typo3.org/extension

Configuration
-------------

Backend
~~~~~~~
1. Edit your root page
2. Switch to "Resources" Tab
3. Add the Page TSConfig file from the |ext_key| extension

    * There might be multiple files for each gridsystem. Please only include one file.

Frontend
~~~~~~~~
1. Edit your `Template` record
2. Switch to "Includes" tab
3. Add the TypoScript file from the |ext_key| extension

    * There might be multiple files for each gridsystem. Please only include one file.

.. |ext_key| replace:: :code:`column_layout`
