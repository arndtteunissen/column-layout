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
use TYPO3\CMS\Extbase\Object\ObjectManager;
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

    /**
     * @return GridRenderer
     */
    public static function getInstance()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        return $objectManager->get(static::class);
    }

    /**
     * @param GridSystemTemplateService $templateService
     */
    public function injectTemplateService(GridSystemTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Renders a grid row.
     * Initializes a new grid state for this row.
     * After its content has been rendered it automatically closes the row after the last content element.
     *
     * @param int $colPos backend layout colPos
     * @param \Closure $renderContentClosure content inside the row
     * @param array $additionalArguments passed to template rendering
     * @return string row HTML
     */
    public function renderRow(int $colPos, \Closure $renderContentClosure, array $additionalArguments = [])
    {
        if (!$this->shouldRenderRow($colPos)) {
            return $renderContentClosure();
        }

        $output = '';

        // Initialize state
        if (!isset($this->state[$colPos])) {
            $this->state[$colPos] = [
                'enabled' => true,
                'has_row_began' => false,
                'has_row_end' => false
            ];
        }

        $state = &$this->state[$colPos];

        $variables = [
            'state' => &$state,
            'arguments' => $additionalArguments
        ];

        // Render child content
        $output .= $renderContentClosure();

        /*
         * IV) Render closing row html, if
         * 1. content has been rendered
         * 2. row has been opened before
         * 3. row has not been closed before
         */
        if ($output
            && $state['has_row_began']
            && !$state['has_row_end']) {
            $output .= $this->templateService->renderRowEndHtml($colPos, $variables);
        }

        // Reset state
        $this->state[$colPos] = [];

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
     * Renders a grid column.
     * Renders row closing and opening html before the given content element based on the state.
     * As this method heavily depends on the grid state it is crucial to call this only if the
     * GridRenderer::renderRow method has been called before!
     *
     * @param array $record tt_content record of the content to be rendered
     * @param \Closure $renderContentClosure content inside the column
     * @param array $additionalArguments passed to template rendering
     * @return string column HTML
     */
    public function renderColumn(array $record, \Closure $renderContentClosure, array $additionalArguments = [])
    {
        if (!$this->shouldRenderColumn($record)) {
            return $renderContentClosure();
        }

        $output = '';

        // Get state
        $colPos = $record['colPos'];
        $state = &$this->state[$colPos];

        // Fetch config for column
        $currentLayoutConfig = $this->getColumnLayoutConfig($record);

        $variables = [
            'settings' => $currentLayoutConfig,
            'state' => &$state,
            'data' => $record,
            'arguments' => $additionalArguments
        ];

        /*
         * I) Render the closing row html, if
         * 1. a row has been opened before,
         * 2. a row has not been closed before
         * 3. this element requires a new row (including fullwidth)
         */
        if ($state['has_row_began']
            && !$state['has_row_end']
            && ((int)$currentLayoutConfig['row_fullwidth'] === 1
                || (int)$currentLayoutConfig['row_behaviour'] === 1)) {
            $output .= $this->templateService->renderRowEndHtml($colPos, $variables);
            $state['has_row_began'] = false;
            $state['has_row_end'] = true;
        }

        /*
         * II) Render the opening row html, if
         * 1. this is the first element, or
         * 2. this element requires a new row
         *      1. a row has been closed before
         */
        if (!$state['has_row_began']
            || ($state['has_row_end']
                && ((int)$currentLayoutConfig['row_fullwidth'] === 1
                    || (int)$currentLayoutConfig['row_behaviour'] === 1))) {
            $output .= $this->templateService->renderRowBeginHtml($colPos, $variables);
            $state['has_row_began'] = true;
            $state['has_row_end'] = false;
        }

        // III) Render column wrap
        $content = new RenderableClosure();
        $content
            ->setName('column-content')
            ->setClosure($renderContentClosure);

        $variables['column_content'] = $content;

        $output .= $this->templateService->renderColumnHtml($variables);

        return $output;
    }

    /**
     * Decides whether a column should be rendered based on the grid rendering state.
     *
     * @param array $record
     * @return bool
     */
    public function shouldRenderColumn(array $record): bool
    {
        $colPos = $record['colPos'];

        return !empty($this->state[$colPos]['enabled']);
    }

    /**
     * Return the column layout flexform configuration from current element as array.
     *
     * @param array $record
     * @return array
     */
    protected function getColumnLayoutConfig(array $record): array
    {
        // TODO: implement caching
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);

        return $flexFormService->convertFlexFormContentToArray($record['tx_column_layout_column_config']);
    }
}
