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

        if (!empty($layoutConfiguration['small_width'])) {
            $classes[] = 'col-' . $layoutConfiguration['small_width'];
        }
        if (!empty($layoutConfiguration['medium_width'])) {
            $classes[] = 'col-md-' . $layoutConfiguration['medium_width'];
        }
        if (!empty($layoutConfiguration['large_width'])) {
            $classes[] = 'col-lg-' . $layoutConfiguration['large_width'];
        }

        if (!empty($layoutConfiguration['small_offset'])) {
            $classes[] = 'offset-' . $layoutConfiguration['small_offset'];
        }
        if (isset($layoutConfiguration['medium_offset'])) {
            $classes[] = 'offset-md-' . $layoutConfiguration['medium_offset'];
        }
        if (isset($layoutConfiguration['large_offset'])) {
            $classes[] = 'offset-lg-' . $layoutConfiguration['large_offset'];
        }

        if (!empty($layoutConfiguration['small_order'])) {
            $classes[] = 'order-' . $layoutConfiguration['small_order'];
        }
        if (!empty($layoutConfiguration['medium_order'])) {
            $classes[] = 'order-md-' . $layoutConfiguration['medium_order'];
        }
        if (!empty($layoutConfiguration['large_order'])) {
            $classes[] = 'order-lg-' . $layoutConfiguration['large_order'];
        }

        return implode(' ', $classes);
    }
}
