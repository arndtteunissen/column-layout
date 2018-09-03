<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * This abstract ViewHelper generalize common methods and properties of the grid system ViewHelpers.
 */
abstract class AbstractGridViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

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
        return static::isGridRenderingEnabled($arguments, $renderingContext)
            ? static::wrapContent($arguments, $renderChildrenClosure, $renderingContext)
            : $renderChildrenClosure();
    }

    /**
     * Renders the grid system around the view helper's content.
     * Arguments are passed from the renderStatic method.
     *
     * @see AbstractViewHelper::renderStatic
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    protected abstract static function wrapContent(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext);

    /**
     * @return GridSystemTemplateService
     */
    protected static function getTemplateService(): GridSystemTemplateService
    {
        return GeneralUtility::makeInstance(GridSystemTemplateService::class);
    }

    /**
     * Decide whether grid rendering should be enabled.
     *
     * @param array $arguments ViewHelper arguments
     * @param RenderingContextInterface $context the current rendering context
     * @return bool TRUE if the view helper should render the grid
     */
    protected abstract static function isGridRenderingEnabled(array $arguments, RenderingContextInterface $context): bool;
}