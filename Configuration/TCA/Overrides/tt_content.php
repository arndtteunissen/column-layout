<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die();

$ll = 'LLL:EXT:column_layout/Resources/Private/Language/locallang_db.xlf:';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'tx_column_layout_column_config' => [
        'label' => $ll . 'tt_content.tx_column_layout_column_config.label',
        'config' => [
            'type' => 'flex',
            'ds' => [
                'default' => 'FILE:EXT:column_layout/Configuration/FlexForms/GridSystems/Foundation.xml'
            ]
        ]
    ]
]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;' . $ll . 'tt_content.tabs.tx_column_layout_column_config,tx_column_layout_column_config',
    '',
    'after:categories'
);
