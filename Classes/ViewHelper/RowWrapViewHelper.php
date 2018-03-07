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
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT'])) {
            $GLOBALS['TX_COLUMN_LAYOUT'] = [];
        }

        $GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] = 1;

        $output = $renderChildrenClosure();

        if ($GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] != 1) {
            // End row after last column
            $typoScript = self::getTypoScript();
            $cObj = self::getCObj();
            $output .= $cObj->cObjGetSingle(
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end'],
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end.']
            );
        }

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