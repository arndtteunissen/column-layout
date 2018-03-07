<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile('column_layout', 'Configuration/TSConfig/column_layout.tsconfig', 'Column Layout Gridsystem');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile('column_layout', 'Configuration/TSConfig/GridSystems/foundation.tsconfig', 'Gridsystem :: Foundation');
