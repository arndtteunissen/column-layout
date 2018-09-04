<?php
namespace Arndtteunissen\ColumnLayout\Service;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * A templating service for rendering the grid system templates which render row and column html.
 * This service replaces the need of the ContentObjectRenderer to render the FLUIDTEMPLATE.
 * It adapts some features of the FluidTemplateContentObject (i.e. template paths and DataProcessors).
 * This service is bound to the TypoScript setup/configuration under path 'lib.tx_column_layout.rendering.'
 */
class GridSystemTemplateService implements SingletonInterface
{
    const SECTION_NAME_ROW = 'Row';
    const SECTION_NAME_COLUMN = 'Column';

    const SECTION_NAME_ROW_BEGIN = 'tx_column_layout_Row/Begin';
    const SECTION_NAME_ROW_END = 'tx_column_layout_Row/End';

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * Lazy initialized ContentDataProcessor which is used to process rendering variables.
     * Use GridSystemTemplateService::getContentDataProcessor() method to access it.
     *
     * @var ContentDataProcessor
     */
    protected $contentDataProcessor;

    /**
     * GridSystemTemplatingService constructor.
     */
    public function __construct()
    {
        $this->contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->view = $this->initializeViewInstance();
    }

    /**
     * Renders the row beginning html.
     *
     * @param array $variables passed to rendering
     * @return string HTML
     */
    public function renderRowBeginHtml(array $variables = [])
    {
        $variables = $this->applyDataProcessors('row', $variables);

        return $this->view->renderSection(self::SECTION_NAME_ROW_BEGIN, $variables);
    }

    /**
     * Renders the row closing html
     *
     * @param array $variables passed to rendering
     * @return string
     */
    public function renderRowEndHtml(array $variables = [])
    {
        $variables = $this->applyDataProcessors('row', $variables);

        return $this->view->renderSection(self::SECTION_NAME_ROW_END, $variables);
    }

    /**
     * Renders the whole column html
     *
     * @param array $variables passed to rendering
     * @return string
     */
    public function renderColumnHtml(array $variables = [])
    {
        $variables = $this->applyDataProcessors('column', $variables);

        return $this->view->renderSection(self::SECTION_NAME_COLUMN, $variables);
    }

    /**
     * Initializes and configures a new View to render the grid system templates.
     *
     * @return StandaloneView
     */
    protected function initializeViewInstance()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class, $this->contentObject);

        // Set paths from TypoScript setup
        $renderingConfiguration = $this->getRenderingConfiguration();
        $view->setLayoutRootPaths($renderingConfiguration['layoutRootPaths.']);
        $view->setPartialRootPaths($renderingConfiguration['partialRootPaths.']);
        $view->setTemplateRootPaths($renderingConfiguration['templateRootPaths.']);
        $view->setTemplate($renderingConfiguration['templateName']);

        return $view;
    }

    /**
     * Runs all DataProcessors for the given context.
     * Please note, that a ContentObjectRenderer is not available inside a DataProcessor.
     *
     * @param string $context rendering context (either 'row' or 'column')
     * @param array $variables
     * @return array processed variables
     */
    protected function applyDataProcessors(string $context, array $variables = [])
    {
        $renderingConfiguration = $this->getRenderingConfiguration();
        if (!empty($renderingConfiguration[$context . '.'])) {
            // Override configuration with context
            $configuration = array_replace_recursive(
                $renderingConfiguration,
                $renderingConfiguration[$context . '.']
            );

            $contentDataProcessor = $this->getContentDataProcessor();
            $variables = $contentDataProcessor->process($this->contentObject, $configuration, $variables);
        }

        return $variables;
    }

    /**
     * Return the TypoScript setup of the current page template.
     *
     * @see FrontendConfigurationManager::getTypoScriptSetup()
     *
     * @return array
     */
    protected function getTypoScript(): array
    {
        return $GLOBALS['TSFE']->tmpl->setup;
    }

    /**
     * Extracts the TypoScript setup/configuration for grid system template rendering
     * @return mixed
     */
    protected function getRenderingConfiguration()
    {
        return $this->getTypoScript()['lib.']['tx_column_layout.']['rendering.'];
    }

    /**
     * Lazy instantiates the ContentDataProcessor.
     *
     * @return ContentDataProcessor
     */
    protected function getContentDataProcessor(): ContentDataProcessor
    {
        if ($this->contentDataProcessor === null) {
            $this->contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
        }

        return $this->contentDataProcessor;
    }
}
