<?php
namespace Arndtteunissen\ColumnLayout\Fluid\Core\Parser;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * TemplateParser interceptor calling the closingTagPostParseEvent static member function of ViewHelpers
 * after the closing ViewHelper tag was parsed.
 * This makes it possible to access the child nodes of a ViewHelperNode which is not possible inside the
 * \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper::postParseEvent member function.
 *
 * Please note that this class implements the deprecated InterceptorInterface because it would not be able to register
 * this interceptor automatically inside the \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext::buildParserConfiguration.
 *
 * @see \TYPO3Fluid\Fluid\Core\Parser\TemplateParser::closingViewHelperTagHandler
 * @see \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper::postParseEvent
 * @see \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext::buildParserConfiguration
 */
class ViewHelperClosingTagPostParseEventEmitter implements InterceptorInterface
{
    /**
     * The interceptor can process the given node at will and must return a node
     * that will be used in place of the given node.
     *
     * @param NodeInterface $node
     * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
     * @param ParsingState $parsingState the parsing state
     * @return NodeInterface
     */
    public function process(NodeInterface $node, $interceptorPosition, ParsingState $parsingState)
    {
        if (!$node instanceof ViewHelperNode) {
            return $node;
        }

        $viewHelperClassName = $node->getViewHelperClassName();

        if (method_exists($viewHelperClassName, 'closingTagPostParseEvent')) {
            $viewHelperClassName::closingTagPostParseEvent($node, $node->getArguments(), $parsingState->getVariableContainer());
        }

        return $node;
    }

    /**
     * The interceptor should define at which interception positions it wants to be called.
     *
     * @return array Array of INTERCEPT_* constants
     */
    public function getInterceptionPoints()
    {
        return [InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER];
    }
}
