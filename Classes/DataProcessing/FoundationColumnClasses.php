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
class FoundationColumnClasses implements DataProcessorInterface
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

        if (!empty($layoutConfiguration['small_width'])) {
            $classes[] = 'small-' . $layoutConfiguration['small_width'];
        }
        if (!empty($layoutConfiguration['medium_width'])) {
            $classes[] = 'medium-' . $layoutConfiguration['medium_width'];
        }
        if (!empty($layoutConfiguration['large_width'])) {
            $classes[] = 'large-' . $layoutConfiguration['large_width'];
        }

        if (!empty($layoutConfiguration['small_offset'])) {
            $classes[] = 'small-offset-' . $layoutConfiguration['small_offset'];
        }
        if (isset($layoutConfiguration['medium_offset'])) {
            $classes[] = 'medium-offset-' . $layoutConfiguration['medium_offset'];
        }
        if (isset($layoutConfiguration['large_offset'])) {
            $classes[] = 'large-offset-' . $layoutConfiguration['large_offset'];
        }

        if (!empty($layoutConfiguration['small_order'])) {
            $classes[] = 'small-order-' . $layoutConfiguration['small_order'];
        }
        if (!empty($layoutConfiguration['medium_order'])) {
            $classes[] = 'medium-order-' . $layoutConfiguration['medium_order'];
        }
        if (!empty($layoutConfiguration['large_order'])) {
            $classes[] = 'large-order-' . $layoutConfiguration['large_order'];
        }

        return implode(' ', $classes);
    }
}
