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
 * Generates the css column classes for the Bootstrap grid system
 *
 * @link https://getbootstrap.com/docs/4.1/getting-started/introduction/
 */
class BootstrapColumnClasses implements DataProcessorInterface
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
            $classes[] = 'col-' . $layoutConfiguration['sDEF']['small_width'];
        }
        if (!empty($layoutConfiguration['sDEF']['medium_width'])) {
            $classes[] = 'col-md-' . $layoutConfiguration['sDEF']['medium_width'];
        }
        if (!empty($layoutConfiguration['sDEF']['large_width'])) {
            $classes[] = 'col-lg-' . $layoutConfiguration['sDEF']['large_width'];
        }

        if (!empty($layoutConfiguration['sOffsets']['small_offset'])) {
            $classes[] = 'offset-' . $layoutConfiguration['sOffsets']['small_offset'];
        }
        if (isset($layoutConfiguration['sOffsets']['medium_offset'])) {
            $classes[] = 'offset-md-' . $layoutConfiguration['sOffsets']['medium_offset'];
        }
        if (isset($layoutConfiguration['sOffsets']['large_offset'])) {
            $classes[] = 'offset-lg-' . $layoutConfiguration['sOffsets']['large_offset'];
        }

        if (!empty($layoutConfiguration['sOrders']['small_order'])) {
            $classes[] = 'order-' . $layoutConfiguration['sOrders']['small_order'];
        }
        if (!empty($layoutConfiguration['sOrders']['medium_order'])) {
            $classes[] = 'order-md-' . $layoutConfiguration['sOrders']['medium_order'];
        }
        if (!empty($layoutConfiguration['sOrders']['large_order'])) {
            $classes[] = 'order-lg-' . $layoutConfiguration['sOrders']['large_order'];
        }

        if (!empty($layoutConfiguration['sDEF']['additional_layout'])) {
            $classes[] = $layoutConfiguration['sDEF']['additional_layout'];
        }

        return implode(' ', $classes);
    }
}
