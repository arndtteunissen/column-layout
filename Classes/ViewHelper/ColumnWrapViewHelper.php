<?php
namespace Arndtteunissen\ColumnLayout\ViewHelper;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * ViewHelper which wraps content with a column according to the current gridsystem.
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
        $this->registerArgument('columnLayoutKey', 'string', 'Variable name of the injected column layout', false, null);
    }

    /**
     * {@inheritdoc}
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $record = $arguments['record'];
        $flexForm = $record['tx_column_layout_column_config'] ?? false;
        $typoScript = self::getTypoScript();
        $layoutConfiguration = null;
        $rowStart = $GLOBALS['TX_COLUMN_LAYOUT']['rowStart']-- == 1;

        if ($flexForm) {
            $layoutConfiguration = ColumnLayoutUtility::hydrateLayoutConfigFlexFormData($flexForm);

            // Check if manual forcing new row
            $rowStart = $rowStart || (int)$layoutConfiguration['sDEF']['row_behaviour'];
        }

        $as = $arguments['columnLayoutKey'];
        if ($as) {
            $renderingContext->getVariableProvider()->add($as, $layoutConfiguration);
        }

        $content = $renderChildrenClosure();

        if ($as) {
            $renderingContext->getVariableProvider()->remove($as);
        }

        $cObj = self::getCObj();

        // Render column wrap
        $record['tx_column_layout_column_config_orig'] = $record['tx_column_layout_column_config'];
        $record['tx_column_layout_column_config'] = $layoutConfiguration;
        $cObj->start($record);
        $columnWrap = $cObj->cObjGetSingle(
            $typoScript['lib.']['tx_column_layout.']['columnWrap.']['content'],
            $typoScript['lib.']['tx_column_layout.']['columnWrap.']['content.']
        );

        // Wrap content with column
        $output = $cObj->stdWrap_wrap($content, ['wrap' => $columnWrap]);

        // Begin new row before content
        if ($rowStart) {
            $rowWrap = '';
            if ($GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] < 0) {
                $rowWrap .= $cObj->cObjGetSingle(
                    $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end'],
                    $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end.']
                );
            }

            $rowWrap .= $cObj->cObjGetSingle(
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['start'],
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['start.']
            );

            $output = $rowWrap . $output;
        }

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
