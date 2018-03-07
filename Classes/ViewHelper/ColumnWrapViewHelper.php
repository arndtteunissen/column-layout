<?php
namespace Arndtteunissen\ColumnLayout\ViewHelper;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * ViewHelper which wraps content with a column according to the current gridsystem.
 */
class ColumnWrapViewHelper extends AbstractViewHelper
{
    /**
     * @var string key of the classes rendering CObject in typoscript lib.
     */
    protected $typoScriptClassesRenderingLibKey = 'columnLayoutColumnClasses';

    /**
     * Prevent the children output from being escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * This ViewHelper's output is HTML, so it should not be escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObjectRenderer;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param ContentObjectRenderer $contentObjectRenderer
     */
    public function injectContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer)
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeArguments()
    {
        $this->registerArgument('record', 'array', 'Content Element Data', true);
        $this->registerArgument('content', 'mixed', 'Content to be wrapped by the column', false, null);
        $this->registerArgument('additionalClasses', 'string', 'Additional classes to be added to the column wrap', false, null);
        $this->registerArgument('columnLayoutKey', 'string', 'Variable name of the injected column layout', false, null);
    }

    /**
     * Render the output of this ViewHelper
     *
     * @return string
     */
    public function render()
    {
        $record = $this->arguments['record'];
        $tagBuilder = new TagBuilder('div');
        $configuration = $record['tx_column_layout_column_config'] ?? false;
        $layoutConfiguration = null;
        $columnClasses = '';

        if ($configuration) {
            $configuration = GeneralUtility::xml2array($configuration);
            // Hydrate flexform data structure
            $layoutConfiguration = array_map(function ($sheet) {
                return array_map(function($field) {
                    return $field['vDEF'];
                }, $sheet['lDEF']);
            }, $configuration['data']);

            // Render TypoScript
            $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $this->contentObjectRenderer->start($layoutConfiguration);
            $columnClasses = $this->contentObjectRenderer->cObjGetSingle($typoScript['lib.'][$this->typoScriptClassesRenderingLibKey], $typoScript['lib.'][$this->typoScriptClassesRenderingLibKey . '.']);
        }

        if ($this->arguments['additionalClasses']) {
            $columnClasses .= $this->arguments['additionalClasses'];
        }

        $as = $this->arguments['columnLayoutKey'];
        if ($as) {
            $this->templateVariableContainer->add($as, $layoutConfiguration);
        }

        $content = $this->arguments['content'];
        if ($content == null) {
            $content = $this->renderChildren();
        }
        $tagBuilder->setContent($content);

        if ($as) {
            $this->templateVariableContainer->remove($as);
        }

        $tagBuilder->addAttribute('class', trim($columnClasses));

        return $tagBuilder->render();
    }
}