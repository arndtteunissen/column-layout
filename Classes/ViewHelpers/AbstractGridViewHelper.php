<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
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
    public function initialize()
    {
        $this->contentArgumentName = 'content';
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