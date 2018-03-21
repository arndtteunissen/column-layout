<?php
namespace Arndtteunissen\ColumnLayout\ViewHelper;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
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

        // Prepare rendering
        $cObj = self::getCObj();
        $cObj->start($record);

        $content = new RenderableClosure();
        $content
            ->setName('column-content')
            ->setClosure($renderChildrenClosure);

        $template = $typoScript['lib.']['tx_column_layout.']['rendering.']['column'];
        $templateConfig = $typoScript['lib.']['tx_column_layout.']['rendering.']['column.'];

        // Add additional data
        $templateConfig['settings.']['content'] = $content;
        $templateConfig['settings.']['row_begin'] = $GLOBALS['TX_COLUMN_LAYOUT']['rowStart']-- == 1;
        $templateConfig['settings.']['row_end'] = $templateConfig['settings.']['row_begin'] && $GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] < 0;

        // Render template
        $output = $cObj->cObjGetSingle(
            $template,
            $templateConfig
        );

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
