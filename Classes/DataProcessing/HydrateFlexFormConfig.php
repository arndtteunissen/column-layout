<?php
namespace Arndtteunissen\ColumnLayout\DataProcessing;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to hydrate the column configuration flex form data structure.
 */
class HydrateFlexFormConfig implements DataProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (empty($processorConfiguration['field']) || !isset($processedData['data'][$processorConfiguration['field']])) {
            return $processedData;
        }

        $flexFormData = ColumnLayoutUtility::hydrateLayoutConfigFlexFormData(
            $processedData['data'][$processorConfiguration['field']]
        );

        $as = $cObj->stdWrapValue('as', $processorConfiguration, 'column_layout');
        $processedData[$as] = $flexFormData;

        return $processedData;
    }
}
