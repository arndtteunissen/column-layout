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
 * Class NewColumnWrapViewHelper
 */
class ColumnWrapViewHelper extends AbstractGridViewHelper
{
    /**
     * @var string
     */
    protected $contentArgumentName = 'content';

    /**
     * {@inheritdoc}
     */
    public function initializeArguments()
    {
        $this->registerArgument('record', 'array', 'Content Element Data', true);
        $this->registerArgument('content', 'mixed', 'Content to be wrapped by the column', false, null);
        $this->registerArgument('additionalArguments', 'array', 'Will pe passed to rendering of grid templates', false, []);
    }

    /**
     * {@inheritdoc}
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $renderer = GridRenderer::getInstance();

        return $renderer->renderColumn($arguments['record'], $renderChildrenClosure, $arguments['additionalArguments'] ?? []);
    }
}
