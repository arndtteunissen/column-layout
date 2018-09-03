<?php
namespace Arndtteunissen\ColumnLayout\Rendering;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use Arndtteunissen\ColumnLayout\Utility\EmConfigurationUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableClosure;

/**
 * This class is responsible for rendering content into a grid system.
 * It provides public member methods to render a row and a column. This members can be used by several integrations
 * (e.g. ViewHelpers, USER content object, custom content object, etc.).
 */
class GridRenderer implements SingletonInterface
{
    /**
     * @var GridSystemTemplateService
     */
    protected $templateService;

    /**
     * @var array contains the grid rendering state
     */
    protected $state;

    public function __construct()
    {
        $this->templateService = GeneralUtility::makeInstance(GridSystemTemplateService::class);
    }

    /**
     * Renders a grid row
     *
     * @param \Closure $contentClosure content inside the row
     * @return string row HTML
     */
    public function renderRow(\Closure $contentClosure)
    {
        // Setup row context
        $this->state = [
            'enabled' => true,
            'contentElementIndex' => 0,
            'isFullwidthElement' => false
        ];

        // Prepare child content rendering
        $endRow = false;
        $content = new RenderableClosure();
        $content
            ->setName('row-content')
            ->setClosure(function () use ($contentClosure, &$endRow) {
                $output = $contentClosure();
                /*
                 * After content is rendered check for whether to close the row.
                 * Changes the value of a variable passed by reference to the rendering variable container.
                 */
                $endRow = $this->state['contentElementIndex'] > 0;

                return $output;
            });

        // Setup rendering settings
        $settings = [
            'content' => $content,
            'fullscreen' => $this->state['isFullscreenElement'],
            'row_end' => &$endRow
        ];

        // Render template
        $output = $this->templateService->renderRowHtml([
            'settings' => $settings
        ]);

        $this->state = [];

        return $output;
    }

    /**
     * Renders a grid column
     *
     * @param \Closure $contentClosure content inside the column
     * @param array $contentRecord tt_content record of the content to be rendered
     * @return string column HTML
     */
    public function renderColumn(\Closure $contentClosure, array $contentRecord)
    {
        $currentLayoutConfig = $this->getColumnLayoutConfig($contentRecord);

        // Setup rendering settings
        $settings = [
            'row_begin' => false,
            'row_end' => false,
            'fullwidth' => $this->state['isFullwidthElement']
        ];

        // Check if the last element was a fullwidth element. We have to close the column for the new element in that case.
        if ($this->state['isFullwidthElement'] === true) {
            $settings['row_begin'] = true;
            $settings['row_end'] = true;
            $this->state['isFullwidthElement']  = false;
        }

        // Determine, if there should be a new row for this column.
        if ($this->state['contentElementIndex'] === 0) {
            // If is the first element. Force opening a new row - regardless of the configuration.
            $settings['row_begin'] = true;

            if ((int)$currentLayoutConfig['row_fullwidth'] === 1) {
                $this->state['isFullwidthElement'] = true;
            }
        } elseif ((int)$currentLayoutConfig['row_fullwidth'] === 1) {
            // When the element is full with, there has to a be new row.
            $settings['row_begin'] = true;
            $settings['row_end'] = true;
            $this->state['isFullwidthElement'] = true;
        } elseif ((int)$currentLayoutConfig['row_behaviour'] === 1) {
            // Force closing the current row and opening a new one, if configured in element.
            $settings['row_begin'] = true;
            $settings['row_end'] = true;
        }

        // Prepare child content rendering
        $content = new RenderableClosure();
        $content
            ->setName('column-content')
            ->setClosure($contentClosure);

        $settings['content'] = $content;

        // Inject current record
        $variables = [
            'data' => $contentRecord,
            'current' => null
        ];

        // Set settings
        $variables['settings'] = $settings;

        // Render template
        $output = $this->templateService->renderColumnHtml($variables);

        // Raise index of content elements.
        $this->state['contentElementIndex']++;

        return $output;
    }

    /**
     * Decides whether a row should be rendered based on the given colPos.
     *
     * @param int $colPos backend layout colPos
     * @return bool TRUE if it is enabled
     */
    public function shouldRenderRow(int $colPos): bool
    {
        $emConfig = EmConfigurationUtility::getSettings();

        return !in_array($colPos, $emConfig->getColPosListForDisable());
    }

    /**
     * Decides whether a column should be rendered based on the grid rendering state.
     *
     * @return bool
     */
    public function shouldRenderColumn(): bool
    {
        return !empty($this->state['enabled']);
    }

    /**
     * Return the column layout flexform configuration from current element as array.
     *
     * @param array $record
     * @return array
     */
    protected function getColumnLayoutConfig(array $record): array
    {
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);

        return $flexFormService->convertFlexFormContentToArray($record['tx_column_layout_column_config']);
    }
}