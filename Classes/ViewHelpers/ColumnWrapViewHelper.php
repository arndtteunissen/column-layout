<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableClosure;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class NewColumnWrapViewHelper
 */
class ColumnWrapViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * Prevent the children output from being escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * This ViewHelper's output is HTML, so it should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->contentArgumentName = 'content';
    }

    /**
     * {@inheritdoc}
     */
    public function initializeArguments()
    {
        $this->registerArgument('record', 'array', 'Content Element Data', true);
        $this->registerArgument('content', 'mixed', 'Content to be wrapped by the column', false, null);
    }

    /**
     * {@inheritdoc}
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $record = $arguments['record'];
        $typoScript = self::getTypoScript();
        $currentLayoutConfig = self::getColumnLayoutConfig($record);


        // Get the typoscript config to render the template.
        $template = $typoScript['lib.']['tx_column_layout.']['rendering.']['column'];
        $templateConfig = $typoScript['lib.']['tx_column_layout.']['rendering.']['column.'];

        // Prepare rendering
        $cObj = self::getCObj();
        $cObj->start($record);

        // Reset the row states variables.
        $templateConfig['settings.']['row_begin'] = false;
        $templateConfig['settings.']['row_end'] = false;

        // Check if the last element was a fullwidth element. We have to close the column for the new element in that case.
        if ($GLOBALS['TX_COLUMN_LAYOUT']['isFullwidthElement'] === true) {
            $templateConfig['settings.']['row_begin'] = true;
            $templateConfig['settings.']['row_end'] = true;
            $GLOBALS['TX_COLUMN_LAYOUT']['isFullwidthElement']  = false;
        }

        // Determine, if there should be a new row for this column.
        if ($GLOBALS['TX_COLUMN_LAYOUT']['contentElementIndex'] === 0) {
            // If is the first element. Force opening a new row - regardless of the configuration.
            $templateConfig['settings.']['row_begin'] = true;

            if ((int)$currentLayoutConfig['row_fullwidth'] === 1) {
                $GLOBALS['TX_COLUMN_LAYOUT']['isFullwidthElement'] = true;
            }
        } elseif ((int)$currentLayoutConfig['row_fullwidth'] === 1) {
            // When the element is full with, there has to a be new row.
            $templateConfig['settings.']['row_begin'] = true;
            $templateConfig['settings.']['row_end'] = true;
            $GLOBALS['TX_COLUMN_LAYOUT']['isFullwidthElement'] = true;
        } elseif ((int)$currentLayoutConfig['row_behaviour'] === 1) {
            // Force closing the current row and opening a new one, if configured in element.
            $templateConfig['settings.']['row_begin'] = true;
            $templateConfig['settings.']['row_end'] = true;
        }

        $content = new RenderableClosure();
        $content
            ->setName('column-content')
            ->setClosure($renderChildrenClosure);

        // Add the content to render to the template.
        $templateConfig['settings.']['content'] = $content;
        $templateConfig['settings.']['fullwidth'] = $GLOBALS['TX_COLUMN_LAYOUT']['isFullwidthElement'];

        // Render template
        $output = $cObj->cObjGetSingle(
            $template,
            $templateConfig
        );

        // Raise index of content elements.
        $GLOBALS['TX_COLUMN_LAYOUT']['contentElementIndex']++;

        return $output;
    }

    /**
     * Returns a new ContentObjectRenderer
     * Please note, that the ContentObjectRenderer is not a singleton, so each time this function gets called, a new
     * cObj will be created.
     *
     * @return ContentObjectRenderer
     */
    protected static function getCObj(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * Return the TypoScript setup of the current page template.
     *
     * @see FrontendConfigurationManager::getTypoScriptSetup()
     *
     * @return array
     */
    protected static function getTypoScript(): array
    {
        return $GLOBALS['TSFE']->tmpl->setup;
    }

    /**
     * Return the column layout flexform configuration from current element as array.
     *
     * @param array $record
     * @return array
     */
    protected static function getColumnLayoutConfig(array $record): array
    {
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);

        return $flexFormService->convertFlexFormContentToArray($record['tx_column_layout_column_config']);
    }
}
