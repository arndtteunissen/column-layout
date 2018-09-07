<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers\Template;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * This ViewHelper is used to mark the content area inside a grid row.
 * It does nothing because the row wrap is applied in column rendering.
 */
class RowContentViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Empty render method
     *
     * @return string empty
     */
    public function render()
    {
        return GridSystemTemplateService::ROW_SPLIT_MARKER;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return sprintf('\'%s\'', GridSystemTemplateService::ROW_SPLIT_MARKER);
    }
}
