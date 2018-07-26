<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die();

$ll = 'LLL:EXT:column_layout/Resources/Private/Language/locallang_db.xlf:';
$colPosForDisable = \Arndtteunissen\ColumnLayout\Utility\EmConfigurationUtility::getSettings()->getColPosListForDisable();

$columns = [
    'tx_column_layout_column_config' => [
        'label' => $ll . 'tt_content.tx_column_layout_column_config.label',
        'config' => [
            'type' => 'flex',
            'ds' => [
                'foundation' => 'FILE:EXT:column_layout/Configuration/FlexForms/GridSystems/Foundation.xml',
                'bootstrap' => 'FILE:EXT:column_layout/Configuration/FlexForms/GridSystems/Bootstrap.xml'
            ]
        ]
    ]
];

if ($colPosForDisable) {
    $columns['tx_column_layout_column_config']['displayCond'] = 'FIELD:colPos:!IN:' . implode(',', $colPosForDisable);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;' . $ll . 'tt_content.tabs.tx_column_layout_column_config,tx_column_layout_column_config',
    '',
    'after:categories'
);
