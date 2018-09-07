<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers\Template;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * This ViewHelper is used to mark content inside the grid templates.
 * It just renders its children.
 */
class ContentViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return string Rendered string
     */
    public function render()
    {
        return $this->renderChildren();
    }

    /**
     * {@inheritdoc}
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return sprintf('%s()', $closureName);
    }
}
