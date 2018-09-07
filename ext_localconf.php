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
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][] = \Arndtteunissen\ColumnLayout\Hook\LayoutPreviewHook::class . '->injectStylesAndScripts';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawFooter'][] = \Arndtteunissen\ColumnLayout\Hook\LayoutPreviewHook::class;

    // Extend FormData processing for flexforms as quickfix
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Arndtteunissen\ColumnLayout\Form\FormDataProvider\SaveEffectivePidInDatabaseRow::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseEffectivePid::class
        ]
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class]['depends'][] = \Arndtteunissen\ColumnLayout\Form\FormDataProvider\SaveEffectivePidInDatabaseRow::class;

    // Register Fluid parser interceptors
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['interceptors']['ViewHelperClosingTagPostParseEventEmitter'] = \Arndtteunissen\ColumnLayout\Fluid\Core\Parser\ViewHelperClosingTagPostParseEventEmitter::class;

    // Cache Configuration
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService::TEMPLATES_CACHE_NAME] = array_replace_recursive(
        // Default configuration
        [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\StringFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend::class,
            'options' => [
                'defaultLifetime' => 0,
            ],
            'groups' => [
                'all',
                'pages'
            ]
        ],
        // Administrator configuration or empty
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService::TEMPLATES_CACHE_NAME] ?? []
    );
}, $_EXTKEY);
