<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\EmConfigurationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableClosure;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper which wraps content with a row according to the current gridsystem.
 */
class RowWrapViewHelper extends AbstractGridViewHelper
{
    /**
     * @var string
     */
    protected $contentArgumentName = 'content';

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('colPos', 'int', 'Specify the colPos that should be rendered', false, 0);
        $this->registerArgument('content', 'mixed', 'Content to be wrapped by the column', false, null);
    }

    /**
     * {@inheritdoc}
     */
    protected static function wrapContent(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        // Setup row context
        $GLOBALS['TX_COLUMN_LAYOUT'] = [
            'enabled' => true,
            'contentElementIndex' => 0,
            'isFullwidthElement' => false
        ];

        // Get grid system template service
        $templateService = static::getTemplateService();

        // Prepare child content rendering
        $endRow = false;
        $content = new RenderableClosure();
        $content
            ->setName('row-content')
            ->setClosure(function () use ($renderChildrenClosure, &$endRow) {
                $output = $renderChildrenClosure();
                /*
                 * After content is rendered check for whether to close the row.
                 * Changes the value of a variable passed by reference to the rendering variable container.
                 */
                $endRow = $GLOBALS['TX_COLUMN_LAYOUT']['contentElementIndex'] > 0;

                return $output;
            });

        // Setup rendering settings
        $settings = [
            'content' => $content,
            'fullscreen' => $GLOBALS['TX_COLUMN_LAYOUT']['isFullscreenElement'],
            'row_end' => &$endRow
        ];

        // Render template
        $output = $templateService->renderRowHtml([
            'settings' => $settings
        ]);

        unset($GLOBALS['TX_COLUMN_LAYOUT']);

        return $output;
    }

    /**
     * Decide whether grid rendering should be enabled based on the backend layout configuration.
     *
     * @param array $arguments ViewHelper arguments
     * @param RenderingContextInterface $context the current rendering context
     * @return bool TRUE if the view helper should render the grid
     */
    protected static function isGridRenderingEnabled(array $arguments, RenderingContextInterface $context): bool
    {
        $emConfig = EmConfigurationUtility::getSettings();

        return !in_array($arguments['colPos'], $emConfig->getColPosListForDisable());
    }
}
