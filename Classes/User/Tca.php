<?php
namespace Arndtteunissen\ColumnLayout\User;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Lang\LanguageService;

/**
 * TCA helper/user functions for field items generation
 */
class Tca
{
    /**
     * Generates select items for the gridsystem flexform
     *
     * @param array $params
     * @throws Exception when size or type was not defined
     */
    public function generateSelectItems(array &$params)
    {
        $pageUid = $params['flexParentDatabaseRow']['_tx_column_layout_orig_pid'];
        $size = $params['config']['txColumnLayout']['size'];
        $type = $params['config']['txColumnLayout']['type'];

        $columnSizes = ColumnLayoutUtility::getSizesFor($size, $type, $pageUid);

        $settings = ColumnLayoutUtility::getColumnLayoutSettings($pageUid);
        $sizeSettings = $settings['sizes.'][$size . '.'];
        $typeSettings = $settings['types.'][$type . '.'];
        $itemLabel = $this->getLanguageService()->sL($typeSettings['itemLabel']);

        $items = array_map(function ($column) use ($size, $itemLabel) {
            return [
                sprintf(
                    $itemLabel,
                    $column,
                    $size
                ),
                $column
            ];
        }, $columnSizes);

        if ($columnSizes[0] === 0 && ($typeSettings['itemLabelDisabled'] ?? false)) {
            $items[0][0] = sprintf($this->getLanguageService()->sL($typeSettings['itemLabelDisabled']), $type);
        }

        $typeOrdering = $typeSettings['ordering'] ?? null;
        $sizeTypeOverride = array_key_exists($type . '.', $sizeSettings) ? $sizeSettings[$type . '.']['ordering'] ?? false : false;
        $typeOrdering = $sizeTypeOverride ? $sizeTypeOverride : $typeOrdering;
        if ($typeOrdering === 'reverse') {
            $items = array_reverse($items);
        }

        $params['items'] = $items;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
