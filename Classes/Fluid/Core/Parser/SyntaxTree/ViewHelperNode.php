<?php
namespace Arndtteunissen\ColumnLayout\Fluid\Core\Parser\SyntaxTree;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode as FluidViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Custom ViewHelper node.
 * This bypasses the default ViewHelper resolving behaviour of FluidViewHelperNode but requires a full class name of
 * the ViewHelper.
 *
 * @see FluidViewHelperNode
 */
class ViewHelperNode extends FluidViewHelperNode
{
    /**
     * Constructor.
     *
     * @param string $viewHelperClassName class name of the targeted ViewHelper
     * @param RenderingContextInterface $renderingContext a RenderingContext, provided by invoker
     * @param NodeInterface[] $arguments Arguments of view helper - each value is a RootNode.
     */
    public function __construct(string $viewHelperClassName, RenderingContextInterface $renderingContext, array $arguments)
    {
        $this->viewHelperClassName = $viewHelperClassName;
        $this->arguments = $arguments;
        $resolver = $renderingContext->getViewHelperResolver();
        $this->uninitializedViewHelper = $resolver->createViewHelperInstanceFromClassName($this->viewHelperClassName);
        $this->uninitializedViewHelper->setViewHelperNode($this);
        // Note: RenderingContext required here though replaced later. See https://github.com/TYPO3Fluid/Fluid/pull/93
        $this->uninitializedViewHelper->setRenderingContext($renderingContext);
        $this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
        $this->rewriteBooleanNodesInArgumentsObjectTree($this->argumentDefinitions, $this->arguments);
        $this->validateArguments($this->argumentDefinitions, $this->arguments);
    }
}
