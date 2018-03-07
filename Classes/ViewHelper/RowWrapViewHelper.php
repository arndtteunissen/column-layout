<?php
namespace Arndtteunissen\ColumnLayout\ViewHelper;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper which wraps content with a row according to the current gridsystem.
 */
class RowWrapViewHelper extends AbstractViewHelper
{
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
     * Render the output of this ViewHelper
     *
     * @return string
     */
    public function render()
    {
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT'])) {
            $GLOBALS['TX_COLUMN_LAYOUT'] = [];
        }

        $GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] = 1;

        $output = $this->renderChildren();

        if ($GLOBALS['TX_COLUMN_LAYOUT']['rowStart'] != 1) {
            // End row after last column
            $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $output .= $this->contentObjectRenderer->cObjGetSingle(
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end'],
                $typoScript['lib.']['tx_column_layout.']['rowWrap.']['end.']
            );
        }

        unset($GLOBALS['TX_COLUMN_LAYOUT']['rowStart']);

        return $output;
    }
}