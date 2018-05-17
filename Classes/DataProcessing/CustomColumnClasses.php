<?php
namespace Arndtteunissen\ColumnLayout\DataProcessing;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Generates the css column classes for the Foundation grid system
 *
 * @link https://foundation.zurb.com/sites/docs/xy-grid.html
 */
class CustomColumnClasses implements DataProcessorInterface
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
        if (empty($processorConfiguration['layoutConfig']) || empty($processedData[$processorConfiguration['layoutConfig']])) {
            return $processedData;
        }

        $layoutConfig = $processedData[$processorConfiguration['layoutConfig']];

        $as = $cObj->stdWrapValue('as', $processorConfiguration, 'column_layout');
        $processedData[$as] = $this->generateClasses($layoutConfig);

        return $processedData;
    }

    /**
     * @param array $layoutConfiguration
     * @return string
     */
    protected function generateClasses(array $layoutConfiguration): string
    {
        $classes = [];

        if (!empty($layoutConfiguration['sDEF']['small_width'])) {
            $classes[] = 'sm-' . $layoutConfiguration['sDEF']['small_width'];
        }
        if (!empty($layoutConfiguration['sDEF']['medium_width'])) {
            $classes[] = 'md-' . $layoutConfiguration['sDEF']['medium_width'];
        }
        if (!empty($layoutConfiguration['sDEF']['large_width'])) {
            $classes[] = 'lg-' . $layoutConfiguration['sDEF']['large_width'];
        }

        if (!empty($layoutConfiguration['sOffsets']['small_offset'])) {
            $classes[] = 'sm-offset-' . $layoutConfiguration['sOffsets']['small_offset'];
        }
        if (!empty($layoutConfiguration['sOffsets']['medium_offset'])) {
            $classes[] = 'md-offset-' . $layoutConfiguration['sOffsets']['medium_offset'];
        }
        if (!empty($layoutConfiguration['sOffsets']['large_offset'])) {
            $classes[] = 'lg-offset-' . $layoutConfiguration['sOffsets']['large_offset'];
        }

        if (!empty($layoutConfiguration['sOrders']['small_order'])) {
            $classes[] = 'sm-order-' . $layoutConfiguration['sOrders']['small_order'];
        }
        if (!empty($layoutConfiguration['sOrders']['medium_order'])) {
            $classes[] = 'md-order-' . $layoutConfiguration['sOrders']['medium_order'];
        }
        if (!empty($layoutConfiguration['sOrders']['large_order'])) {
            $classes[] = 'lg-order-' . $layoutConfiguration['sOrders']['large_order'];
        }

        return implode(' ', $classes);
    }
}
