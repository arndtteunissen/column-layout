<?php
namespace Arndtteunissen\ColumnLayout\Hook;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawFooterHookInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * A hook for adding column layout information to content elements in backend "Page" module.
 */
class LayoutPreviewHook implements PageLayoutViewDrawFooterHookInterface, SingletonInterface
{
    /**
     * Hook for header rendering of the PageLayoutController to inject stylesheet required for custom column layout
     * display.
     *
     * @see PageLayoutController::renderContent()
     *
     * @param array $params
     * @param PageLayoutController $ref
     * @return string html to be added to the page layout
     */
    public function injectStyleSheet(array $params, PageLayoutController $ref): string
    {
        $ref->getModuleTemplate()->getPageRenderer()->addCssFile('EXT:column_layout/Resources/Public/Css/web_layout.css');

        return '';
    }

    /**
     * {@inheritdoc}
     * @param array $info
     */
    public function preProcess(PageLayoutView &$parentObject, &$info, array &$row)
    {
        $tsConf = $parentObject->modTSconfig['column_layout'] ?? [];

        if (
            $tsConf['hidePreview'] ?? false                     // Hidden via page TSConfig
            || empty($row['tx_column_layout_column_config'])    // Not set
            || $tsConf['disabled'] ?? false                     // Hidden for this element
        ) {
            return;
        }

        if (!isset($GLOBALS['TX_COLUMN_LAYOUT'])) {
            $GLOBALS['TX_COLUMN_LAYOUT'] = [];
        }
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'])) {
            $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'] = [];
        }
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$row['colPos']])) {
            $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$row['colPos']] = 0;
        }

        $maxColumns = (int)ColumnLayoutUtility::getColumnLayoutSettings($row['pid'])['columnsCount'];
        $previousOffset = $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$row['colPos']];

        $layoutConfiguration = ColumnLayoutUtility::hydrateLayoutConfigFlexFormData($row['tx_column_layout_column_config']);

        $width = $layoutConfiguration['sDEF']['large_width'];
        $offset = $layoutConfiguration['sOffsets']['large_offset'];

        // Calculate offset
        $fullwidth = (bool)$layoutConfiguration['sDEF']['row_fullwidth'];
        $totalOffset = $previousOffset + $offset;
        $totalWidth = $totalOffset + $width;
        if (
            $layoutConfiguration['sDEF']['row_behaviour']
            || $totalWidth > $maxColumns
        ) {
            $totalOffset = $offset;
            $totalWidth = $offset + $width;
        }

        // Fill row if no width is given
        if ($width == 0) {
            $width = $maxColumns - $totalOffset;
            $totalWidth = $maxColumns;
        }

        // Render
        $info[] = $this->renderColumnPreviewRow($width, $totalOffset, $maxColumns - $totalWidth, $fullwidth, $row['pid']);

        // Finish current row for fullscreen elements. There should not be another element in that row.
        if ($fullwidth) {
            // Set total width to max to tell the following content element that it has to start in a new row.
            $totalWidth = $maxColumns;
        }
        $info[] = $this->generateGridCss($row['uid'], $maxColumns, $width, $offset, $fullwidth);

        // Update column offset counter
        $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$row['colPos']] = $totalWidth;
    }

    /**
     * @param int $width
     * @param int $offset
     * @param int $fill
     * @param bool $fullwidth
     * @param int $pid
     * @return string
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function renderColumnPreviewRow($width, $offset, $fill, $fullwidth, $pid): string
    {
        $html = '<div class="column-layout-container">';

        $html .= $this->renderColumnPreviewBoxes($width, $offset, $fill);

        $widthLabel = $this->getLanguageService()->sL(ColumnLayoutUtility::getColumnLayoutSettings($pid)['types.']['widths.']['label']);
        $offsetLabel = $this->getLanguageService()->sL(ColumnLayoutUtility::getColumnLayoutSettings($pid)['types.']['offsets.']['label']);
        $fullwidthLabel = $this->getLanguageService()->sL(ColumnLayoutUtility::getColumnLayoutSettings($pid)['types.']['fullwidth.']['label']);

        $html .= '<div class="column-info-container">';
        $html .= sprintf('<span>%s: %d</span>', $widthLabel, $width);
        $html .= ' | ' . sprintf('<span>%s: %d</span>', $offsetLabel, $offset);
        if ($fullwidth) {
            $html .= ' | ' . sprintf('<span>%s</span>', $fullwidthLabel);
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * @param $width
     * @param $offset
     * @param $fill
     * @return string
     */
    protected function renderColumnPreviewBoxes($width, $offset, $fill): string
    {
        $html = '<div class="column-box-container">';

        while ($offset-- > 0) {
            $html .= '<span class="column-box"></span>';
        }

        while ($width-- > 1) {
            $html .= '<span class="column-box active"></span>';
        }
        if ($width == 0) {
            $html .= '<span class="column-box active last"></span>';
        }

        while ($fill-- > 0) {
            $html .= '<span class="column-box"></span>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generates the CSS for a content element in PageLayoutView to look like a column
     *
     * @param int $uid
     * @param int $max
     * @param int $width
     * @param int $offset
     * @param bool $fullwidth
     * @return string
     */
    protected function generateGridCss($uid, $max, $width, $offset, $fullwidth): string
    {
        return sprintf(
            '<style type="text/css">%s</style>',
            $this->generateCEColumnCSS($uid, $max, $width, $offset, $fullwidth)
        );
    }

    /**
     * @param int $uid
     * @param int $max
     * @param int $width
     * @param int $offset
     * @param bool $fullwidth
     * @return string
     */
    protected function generateCEColumnCSS($uid, $max, $width, $offset, $fullwidth)
    {
        $template = <<<'CSS'
@media only screen and (min-width: 1024px) {
    #element-tt_content-%d { width: %d%%; margin-right: %d%%; } 
    #element-tt_content-%1$d > .t3-page-ce-dragitem { flex: %d; } 
    #element-tt_content-%1$d::before { flex: %d; content: '%4$s'; }
}
CSS;


        $widthPercent = (($width + $offset) / $max) * 100;
        $css = sprintf(
            $template,
            $uid,
            $widthPercent,
            ($fullwidth) ? 100 - $widthPercent : 0,
            $width,
            $offset
        );

        if (!$offset) {
            $css .= sprintf(
                '@media only screen and (min-width: 1024px) { #element-tt_content-%1$d::before { display: none; } }',
                $uid
            );
        }

        return $css;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
