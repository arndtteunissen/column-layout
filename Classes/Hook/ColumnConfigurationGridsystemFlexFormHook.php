<?php
namespace Arndtteunissen\ColumnLayout\Hook;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Hook into the FlexForm processing to change the flexform data structure according to the current selected grid system.
 *
 * @see FlexFormTools
 */
class ColumnConfigurationGridsystemFlexFormHook implements SingletonInterface
{
    /**
     * Generates a DataStructureIdentifier for the flexform in column configuration field
     *
     * @param array $fieldTca
     * @param string $tableName
     * @param string $fieldName
     * @param array $row
     * @return array
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function getDataStructureIdentifierPreProcess(array $fieldTca, string $tableName, string $fieldName, array $row): array
    {
        if ($tableName != 'tt_content' || $fieldName != 'tx_column_layout_column_config') {
            return [];
        }

        return [
            'type' => 'tca',
            'tableName' => $tableName,
            'fieldName' => $fieldName,
            'dataStructureKey' => ColumnLayoutUtility::getColumnLayoutSettings($row['pid'])['flexFormKey']
        ];
    }
}