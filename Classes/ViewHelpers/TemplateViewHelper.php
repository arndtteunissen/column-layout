<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use Arndtteunissen\ColumnLayout\Fluid\Core\Parser\ViewHelperClosingTagPostParseEventEmitter;
use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use Arndtteunissen\ColumnLayout\ViewHelpers\Template\ContentViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode as FluidViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * ViewHelper used to define grid system templates (i.e. Row, Column).
 * This ViewHelper works like a SectionViewHelper internally but also splits the row rendering into custom section on-parsing-time.
 */
class TemplateViewHelper extends SectionViewHelper
{
    /**
     * Event Hook after the ViewHelperNode tree has been built (i.e. the ViewHelper closing tag was parsed).
     *
     * @see ViewHelperClosingTagPostParseEventEmitter
     *
     * @param FluidViewHelperNode $node
     * @param TextNode[] $arguments
     * @param VariableProviderInterface $variableContainer
     * @return void
     */
    public static function closingTagPostParseEvent(FluidViewHelperNode $node, array $arguments, VariableProviderInterface $variableContainer)
    {
        /** @var $nameArgument TextNode */
        $nameArgument = $arguments['name'];
        $sectionName = $nameArgument->getText();

        // Inject custom sections for rendering row opening and closing html
        if ($sectionName === GridSystemTemplateService::SECTION_NAME_ROW) {
            $content = $node->getChildNodes();

            /** @var NodeInterface[] $rowOpeningNodeStack */
            $rowOpeningNodeStack = [];
            /** @var NodeInterface[] $rowClosingNodeStack */
            $rowClosingNodeStack = [];

            $addToStack = 'rowOpeningNodeStack';
            foreach ($content as $childNode) {
                if ($childNode instanceof FluidViewHelperNode
                    && $childNode->getViewHelperClassName() == ContentViewHelper::class) {
                    // Found content marker. The following nodes are part of the row closing html.
                    $addToStack = 'rowClosingNodeStack';
                    continue;
                }

                array_push($$addToStack, $childNode);
            }

            /** @var TemplateViewHelper $selfTemplateViewHelper */
            $selfTemplateViewHelper = $node->getUninitializedViewHelper();

            // Create and add SectionViewHelper ViewHelperNodes to dynamic sections
            $sections = $variableContainer['1457379500_sections'];
            $sections[GridSystemTemplateService::SECTION_NAME_ROW_BEGIN] = self::createSectionViewHelperNode(
                GridSystemTemplateService::SECTION_NAME_ROW_BEGIN,
                $selfTemplateViewHelper->_getRenderingContext(),
                $rowOpeningNodeStack
            );
            $sections[GridSystemTemplateService::SECTION_NAME_ROW_END] = self::createSectionViewHelperNode(
                GridSystemTemplateService::SECTION_NAME_ROW_END,
                $selfTemplateViewHelper->_getRenderingContext(),
                $rowClosingNodeStack
            );

            $variableContainer['1457379500_sections'] = $sections;
        }
    }

    /**
     * Access the rendering context.
     *
     * @return RenderingContextInterface
     */
    private function _getRenderingContext()
    {
        return $this->renderingContext;
    }

    /**
     * Creates a new SectionViewHelper ViewHelperNode.
     *
     * @param string $sectionName name of the section
     * @param RenderingContextInterface $renderingContext
     * @param NodeInterface[] $childNodes all child nodes which should be included in this section
     * @return FluidViewHelperNode
     */
    protected static function createSectionViewHelperNode(string $sectionName, RenderingContextInterface $renderingContext, array $childNodes): FluidViewHelperNode
    {
        $node = new ViewHelperNode(
            SectionViewHelper::class,
            $renderingContext,
            ['name' => new TextNode($sectionName)]
        );

        foreach ($childNodes as $childNode) {
            $node->addChildNode($childNode);
        }

        return $node;
    }
}
