.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _customize:

Customize
=========
You are able to change the behaviour of the pre-defined gridsystems.
These are designed to be easily customizable.

Backend
-------

If you want to change the behaviour in the backend you need to modify the Page TSConfig under the path :code:`mod.cloumn_layout`.

Make sure you include your TSConfig file after include this extension's.

Gridsystem Columns
~~~~~~~~~~~~~~~~~~
Increase the columns count of the gridsystem:

.. code:: typoscript

    mod.column_layout {
        columnsCount = 24
    }

Change columns
~~~~~~~~~~~~~~
To change the allowed columns for each *size type* you need to override the values like this:

.. code:: typoscript

    mod.column_layout {

        sizes {
            large {
                widths = <restriction>
                offsets = <restriction>
                orders = <restriction>
            }

            ...
        }
    }

Possible values for :code:`<restriction>` are:

* the all selector :code:`*` for any column (an array will be created from 0 to the :code:`columnsCount`)
* a range other numbers delimited by a hyphen :code:`-` (e.g. :code:`0-6`)
* a list of comma-separated numbers (e.g. :code:`2,4,6`)

**Please note** that a 0 must be added to the list in order to allow a disabled value.

Frontend
--------
You can change the rendering of the grid rows and columns by modifying the TypoScript. Have a look at the :code:`lib.tx_column_layout.` path.

Change Row Rendering
~~~~~~~~~~~~~~~~~~~~
The rendering of the row wrapping is split into two parts:

1. Starting a row. Adjust the :code:`lib.tx_column_layout.rowWrap.start` TypoScript
2. Ending a row. Adjust the :code:`lib.tx_column_layout.rowWrap.end` TypoScript

The rendered row html is immediately added before, respectively after the content.

Change Column Rendering
~~~~~~~~~~~~~~~~~~~~~~~
The rendering of the column is defined in :code:`lib.tx_column_layout.columnWrap.content`.

Its value is passed along with the content to the `TypoScript wrap function`_. The delimiter is :code:`|` always.

.. _`TypoScript wrap function`: https://docs.typo3.org/typo3cms/TyposcriptReference/DataTypes/Wrap/Index.html
