<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('column_layout', 'Configuration/TypoScript', 'Main Setup');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('column_layout', 'Configuration/TypoScript/GridSystems/Foundation', 'Gridsystem :: Foundation');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('column_layout', 'Configuration/TypoScript/GridSystems/Custom', 'Gridsystem :: Custom');
