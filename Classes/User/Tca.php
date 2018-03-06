<?php

namespace Arndtteunissen\ColumnLayout\User;

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $pageUid = $params['flexParentDatabaseRow']['pid'];
        $size = $params['config']['columnLayoutSize'];
        $type = $params['config']['columnLayoutType'];

        $columnSizes = ColumnLayoutUtility::getSizesFor($size, $type, $pageUid);

        $settings = ColumnLayoutUtility::getColumnLayoutSettings($pageUid);
        $typeSettings = $settings[$type . '.'];
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
