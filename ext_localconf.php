<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die();

call_user_func(function ($extKey) {
    // FlexForm hook for the columns configuration field
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing'][] = \Arndtteunissen\ColumnLayout\Hook\ColumnConfigurationGridsystemFlexFormHook::class;

    // Mod Web>Page layout customizations
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][] = \Arndtteunissen\ColumnLayout\Hook\LayoutPreviewHook::class . '->injectStyleSheet';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawFooter'][] = \Arndtteunissen\ColumnLayout\Hook\LayoutPreviewHook::class;
}, $_EXTKEY);
