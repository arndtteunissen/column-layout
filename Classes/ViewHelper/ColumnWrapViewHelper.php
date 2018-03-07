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

/**
 * ViewHelper which wraps content with a column according to the current gridsystem.
 */
class ColumnWrapViewHelper extends AbstractViewHelper
{
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
        $configuration = $record['tx_column_layout_column_config'] ?? false;
        $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $layoutConfiguration = null;
        $rowStart = $GLOBALS['TX_COLUMN_LAYOUT']['rowStart']-- == 1;

        if ($configuration) {
            $configuration = GeneralUtility::xml2array($configuration);
            // Hydrate flexform data structure
            $layoutConfiguration = array_map(function ($sheet) {
                return array_map(function($field) {
                    return $field['vDEF'];
                }, $sheet['lDEF']);
            }, $configuration['data']);

            // Check if manual forcing new row
            $rowStart = $rowStart || (int)$layoutConfiguration['sDEF']['row_behaviour'];
        }

        $as = $this->arguments['columnLayoutKey'];
        if ($as) {
            $this->templateVariableContainer->add($as, $layoutConfiguration);
        }

        $content = $this->arguments['content'];
        if ($content == null) {
            $content = $this->renderChildren();
        }

        if ($as) {
            $this->templateVariableContainer->remove($as);
        }

        // Render column wrap
        $this->contentObjectRenderer->start($layoutConfiguration);
        $columnWrap = $this->contentObjectRenderer->cObjGetSingle(
            $typoScript['lib.']['tx_column_layout.']['columnWrap.']['content'],
            $typoScript['lib.']['tx_column_layout.']['columnWrap.']['content.']
        );

        // Wrap content with column
        $output = $this->contentObjectRenderer->stdWrap_wrap($content, ['wrap' => $columnWrap]);

        // Begin new row before content
        if ($configuration && $rowStart) {
            $rowWrap = '';
            if ($GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] < 0) {
                $rowWrap .= $this->contentObjectRenderer->cObjGetSingle(
                    $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end'],
                    $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end.']
                );
            }

            $rowWrap .= $this->contentObjectRenderer->cObjGetSingle(
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['start'],
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['start.']
            );

            $output = $rowWrap . $output;
        }

        return $output;
    }
}