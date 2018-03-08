.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _usage:

Usage
=====
In your Fluid templates for the frontend rendering you need to add two view helpers.

Row
~~~
For the row rendering add the :code:`RowWrapViewHelper` around your content elements rendering:

.. code-block:: HTML
        :caption: Templates/Page/Default.html
        :name: row-example

        <html data-namespace-typo3-fluid="true"
              xmlns:layout="http://typo3.org/ns/Arndtteunissen/ColumnLayout/ViewHelper">

        ...

        <main class="content">
            <layout:rowWrap>
                <f:cObject typoscriptObjectPath="lib.dynamicContent" data="{colPos: 1}" />
            </layout:rowWrap>
        </main>

        ...

Column
~~~~~~
Around each content element a column must be wrapped using the :code:`ColumnWrapViewHelper`:

.. code-block:: HTML
        :caption: Layouts/Content/Default.html
        :name: column-example

        <html data-namespace-typo3-fluid="true"
              xmlns:layout="http://typo3.org/ns/Arndtteunissen/ColumnLayout/ViewHelper">

        ...

        <layout:columnWrap record="{data}" columnLayoutKey="column_layout">
            <div class="column-inner column-content">
                <f:render section="content"/>
            </div>
        </layout:columnWrap>

        ...
