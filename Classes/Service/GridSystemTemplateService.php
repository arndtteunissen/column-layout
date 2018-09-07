<?php
namespace Arndtteunissen\ColumnLayout\Service;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\InvalidCacheException;
use TYPO3\CMS\Core\Cache\Exception\InvalidDataException;
use TYPO3\CMS\Core\Cache\Frontend\StringFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
    const TEMPLATES_CACHE_NAME = 'column_layout_grid_templates';
    const CACHE_ROW_SECTION_FORMAT = 'RowSection_%s_%s';

    const SECTION_NAME_ROW = 'Row';
    const SECTION_NAME_COLUMN = 'Column';

    const NAME_ROW_BEGIN = 'RowBegin';
    const NAME_ROW_END = 'RowEnd';

    const ROW_SPLIT_MARKER = '<!-- tx_column_layout_ROW_SPLIT -->';

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
     * Templates cache (stores compiled HTML)
     *
     * @var StringFrontend
     */
    protected $cache;

    /**
     * GridSystemTemplatingService constructor.
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws InvalidCacheException
     */
    public function __construct()
    {
        $this->contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->view = $this->initializeViewInstance();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $cacheManager = $objectManager->get(CacheManager::class);

        $this->cache  = $cacheManager->getCache(self::TEMPLATES_CACHE_NAME);
        if (!$this->cache  instanceof StringFrontend) {
            throw new InvalidCacheException(sprintf('Cache \'%s\' requires a \TYPO3\CMS\Core\Cache\Frontend\StringFrontend', self::TEMPLATES_CACHE_NAME), 1536339417);
        }
    }

    /**
     * Renders the row beginning html.
     *
     * @param string $identifier row identifier, used also as cache identifier
     * @param array $variables passed to rendering
     * @return string HTML
     */
    public function renderRowBeginHtml(string $identifier, array $variables = [])
    {
        return $this->getOrRenderAndStoreRowSection(self::NAME_ROW_BEGIN, $identifier, $variables);
    }

    /**
     * Renders the row closing html
     *
     * @param string $identifier row identifier, used also as cache identifier
     * @param array $variables passed to rendering
     * @return string
     */
    public function renderRowEndHtml(string $identifier, array $variables = [])
    {
        return $this->getOrRenderAndStoreRowSection(self::NAME_ROW_END, $identifier, $variables);
    }

    /**
     * Renders or retrieves the row section from cache
     *
     * @param string $sectionName
     * @param string $identifier identifier of the current row. This will also be used as cache identifier
     * @param array $variables passed to rendering and bound to identifier
     * @return string HTML of the row section
     */
    protected function getOrRenderAndStoreRowSection(string $sectionName, string $identifier, array $variables = [])
    {
        $cacheIdentifier = sprintf(self::CACHE_ROW_SECTION_FORMAT, $sectionName, $identifier);
        if ($this->cache->has($cacheIdentifier)) {
            return $this->cache->get($cacheIdentifier);
        }

        $sections = $this->renderRowSections($variables);

        foreach ($sections as $section => $sectionHTML) {
            try {
                $this->cache->set(
                    sprintf(self::CACHE_ROW_SECTION_FORMAT, $section, $identifier),
                    $sectionHTML
                );
            } catch (InvalidDataException $e) {
                // Do not store cache if it cannot be stored
                // TODO: log invalid cache?
            }
        }

        return $sections[$sectionName];
    }

    /**
     * Renders the actual row sections (begin, end)
     *
     * @param array $variables template variables passed to rendering
     * @return array keys are the begin and end section names
     */
    protected function renderRowSections(array $variables)
    {
        $variables = $this->applyDataProcessors('row', $variables);

        $rowHTML = $this->view->renderSection(self::SECTION_NAME_ROW, $variables);
        $rowSections = explode(self::ROW_SPLIT_MARKER, $rowHTML, 2);

        return [
            self::NAME_ROW_BEGIN => $rowSections[0],
            self::NAME_ROW_END => $rowSections[1]
        ];
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
