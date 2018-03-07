<?php
namespace Arndtteunissen\ColumnLayout\Utility;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class for dealing with column layout settings.
 * Helps to process and fetch the gridsystem.
 */
class ColumnLayoutUtility implements SingletonInterface
{
    /**
     * Calculates the column sizes for the given size and the given size type
     *
     * @param string $size name of the sizes identifier (e.g. large, medium)
     * @param string $type size type (e.g. columns, offset, order)
     * @param int $pageUid the page which's TSConfig should be used
     * @return array of column sizes
     * @throws Exception when the given size or type is not defined
     */
    public static function getSizesFor(string $size, string $type, int $pageUid): array
    {
        $settings = ColumnLayoutUtility::getColumnLayoutSettings($pageUid);

        if (!array_key_exists($size . '.', $settings['sizes.'])) {
            throw new Exception(sprintf('The given size "%s" is not defined in the gridsystem', $size), 1520324173);
        }

        if (!array_key_exists($type, $settings['sizes.'][$size . '.'])) {
            throw new Exception(sprintf('The given type "%s" does not exist for size "%s"', $type, $size), 1520324252);
        }

        return ColumnLayoutUtility::processColumnSizes($settings['sizes.'][$size . '.'][$type], $settings['columnsCount']);
    }

    /**
     * Processes values of size types (e.g. columns, offset, order) and generates an array of column sizes for that type.
     *
     * @param string $values
     * @param int $maxColumns
     * @return array
     */
    protected static function processColumnSizes(string $values, int $maxColumns)
    {
        $columns = [];

        if ($values == '*') {
            $columns = range(0, $maxColumns);
        } else {
            $values = explode(',', $values);
            foreach ($values as $value) {
                if (strpos($value, '-') !== false) {
                    list($from, $to) = explode('-', $value);
                    $columns = array_merge($columns, range($from, $to));
                } else {
                    $columns[] = (int) $value;
                }
            }
        }

        return array_unique($columns);
    }

    /**
     * Returns the column layout configuration from page TSConfig.
     *
     * @param int $page uid of the page
     * @return array column layout settings
     * @throws Exception when the column_layout setting hasn't been defined
     */
    public static function getColumnLayoutSettings(int $page): array
    {
        $pageTSConfig = BackendUtility::getPagesTSconfig($page);

        if (!array_key_exists('column_layout.', $pageTSConfig['mod.'])) {
            throw new Exception(sprintf('No column layout found for page "%d". Please define the column_layout settings in your page TSConfig.', $page), 1520323245);
        }

        return $pageTSConfig['mod.']['column_layout.'];
    }

    /**
     * Simplify a FlexForm DataStructure array.
     * Removes all unnecessary sheet, field or value identifiers.
     *
     * @param string|array $flexFormData either a converted FlexForm array or the raw FlexForm string
     * @return array simplified FlexForm data structure
     */
    public static function hydrateLayoutConfigFlexFormData($flexFormData): array
    {
        $dataStructure = $flexFormData;
        if (is_string($flexFormData)) {
            $dataStructure = GeneralUtility::xml2array($flexFormData);
        }

        // Level: FlexForm Sheets
        return array_map(function ($sheet) {
            // Level: FlexForm Sheet Fields
            return array_map(function($field) {
                // Level: FlexForm Field
                return $field['vDEF'];
            }, $sheet['lDEF']);
        }, $dataStructure['data']);
    }
}