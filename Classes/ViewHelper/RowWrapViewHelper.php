<?php
namespace Arndtteunissen\ColumnLayout\ViewHelper;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableClosure;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper which wraps content with a row according to the current gridsystem.
 */
class RowWrapViewHelper extends AbstractViewHelper
{
    /**
     * This ViewHelper's output is HTML, so it should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Prevent the children output from being escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * {@inheritdoc}
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $typoScript = self::getTypoScript();

        // Setup row context
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT'])) {
            $GLOBALS['TX_COLUMN_LAYOUT'] = [];
        }

        $GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] = 1;

        // Prepare rendering
        $cObj = self::getCObj();
        $rowEnd = false;

        $content = new RenderableClosure();
        $content
            ->setName('row-content')
            ->setClosure(function() use ($renderChildrenClosure, &$rowEnd) {
                $output = $renderChildrenClosure();
                /*
                 * After content is rendered check for whether to close the row.
                 * Changes the value of a variable passed by reference to the rendering variable container.
                 */
                $rowEnd = $GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] != 1;

                return $output;
            });

        $template = $typoScript['lib.']['tx_column_layout.']['rendering.']['row'];
        $templateConfig = $typoScript['lib.']['tx_column_layout.']['rendering.']['row.'];

        // Add additional data
        $templateConfig['settings.']['content'] = $content;
        // The rowEnd variable changes based on the content so it is necessary to pass it by reference
        $templateConfig['settings.']['row_end'] = &$rowEnd;

        // Render template
        $output = $cObj->cObjGetSingle(
            $template,
            $templateConfig
        );

        unset($GLOBALS['TX_COLUMN_LAYOUT']['rowStart']);

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
}
