/**
 * Module: TYPO3/CMS/ColumnLayout/PageActions
 */
define(['jquery','TYPO3/CMS/Backend/PageActions'], function($, PageActions) {
    'use strict';

    var ColumnLayout = {
        settings: {
            isTranslationView: false
        },
        identifier: {
            pageColumns: '.t3js-page-columns'
        }
    };

    ColumnLayout.initializeEvents = function() {
        PageActions.elements.$showHiddenElementsCheckbox.on('change', ColumnLayout.toggleFloatingLayout);
    };

    ColumnLayout.toggleFloatingLayout = function() {
        if (ColumnLayout.isElementFloatingEnabled()) {
            ColumnLayout.enableElementFloating();
        } else {
            ColumnLayout.disableElementFloating();
        }
    };

    ColumnLayout.enableElementFloating = function() {
        var $pageColumns = $(ColumnLayout.identifier.pageColumns);

        $pageColumns.addClass('cl-enable-element-floating');
    };

    ColumnLayout.disableElementFloating = function() {
        var $pageColumns = $(ColumnLayout.identifier.pageColumns);

        $pageColumns.removeClass('cl-enable-element-floating');
    };

    ColumnLayout.isElementFloatingEnabled = function() {
        var $hiddenElements = $(PageActions.identifier.hiddenElements),
            showHiddenRecords = PageActions.elements.$showHiddenElementsCheckbox.prop('checked'),
            translationViewNotActive = !ColumnLayout.settings.isTranslationView;

        return !($hiddenElements.length > 0 && showHiddenRecords) && translationViewNotActive;
    };

    $(function() {
        ColumnLayout.initializeEvents();
        ColumnLayout.toggleFloatingLayout();
    });

    return ColumnLayout;
});
