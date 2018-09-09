<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Rendering\GridRenderer;
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
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $renderer = GridRenderer::getInstance();

        return $renderer->renderRow($arguments['colPos'], $renderChildrenClosure, $arguments['additionalArguments'] ?? []);
    }
}
