<?php
namespace Arndtteunissen\ColumnLayout\Backend;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Lang\LanguageService;

class ColumnRenderer
{
    const CSS_TEMPLATE_WITH_FLOATING = <<<'CSS'
@media only screen and (min-width: 1024px) {
    .cl-enable-element-floating #element-tt_content-%d { width: %d%%; } 
    .cl-enable-element-floating #element-tt_content-%1$d > .t3-page-ce-dragitem { flex: %d; } 
    .cl-enable-element-floating #element-tt_content-%1$d::before { flex: %d; content: 'Offset'; }
}
CSS;

    const CSS_TEMPLATE_WITHOUT_FLOATING = <<<'CSS'
@media only screen and (min-width: 1024px) {
    .cl-enable-element-floating #element-tt_content-%d { width: %d%%; }
}
CSS;

    /**
     * @var array
     */
    protected $row;

    /**
     * @var int
     */
    protected $maxColumns;

    /**
     * @var array
     */
    protected $layoutConfiguration;

    /**
     * @var bool
     */
    protected $startNewRow = false;

    /**
     * @var array
     */
    protected $styles = [];

    /**
     * ColumnRenderer constructor.
     * @param array $row
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function __construct(array $row)
    {
        $this->row = $row;

        if (!isset($GLOBALS['TX_COLUMN_LAYOUT'])) {
            $GLOBALS['TX_COLUMN_LAYOUT'] = [];
        }
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'])) {
            $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'] = [];
        }
        if (!isset($GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$this->row['colPos']])) {
            $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$this->row['colPos']] = 0;
        }

        $this->maxColumns = (int)ColumnLayoutUtility::getColumnLayoutSettings($this->row['pid'])['columnsCount'];
        $this->layoutConfiguration = ColumnLayoutUtility::hydrateLayoutConfigFlexFormData($row['tx_column_layout_column_config']);
    }

    /**
     * @return bool
     */
    public function skipRendering(): bool
    {
        return (bool)$this->row['hidden'];
    }

    /**
     * @return array An array with two keys. 0 is the html markup and 1 is the css.
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function renderSingleColumn(): array
    {
        $htmlAndCss = [];
        $columnWidth = $this->calculateWidth();
        $columnOffset = $this->calculateOffset();

        $offsetInRow = $this->getPreviousOffset();
        $totalWidthInRow = $offsetInRow + $columnOffset + $columnWidth;

        /*
         * Start a new row either when its manually configured or
         * when the new element will not fit into the remaining space in current row.
         */
        if ($this->shouldStartNewRow() || $totalWidthInRow > $this->maxColumns) {
            $this->startNewRow = true;
            $this->setPreviousOffset($columnOffset);
            $offsetInRow = 0;
            $totalWidthInRow = $columnOffset + $columnWidth;
        }

        /*
         * When the element should be a full width element,
         * start also a new row. Also override the configured column width and offset.
         */
        if ($this->isFullwidthRow()) {
            $this->startNewRow = true;
            $this->setPreviousOffset(0);
            $columnOffset = $offsetInRow = 0;
            $columnWidth = $totalWidthInRow = $this->maxColumns;
        }

        $htmlAndCss[] = $this->renderColumnPreviewRow($columnWidth, $offsetInRow, $columnOffset, $this->maxColumns - $totalWidthInRow);
        $htmlAndCss[] = $this->generateFloatingCEColumnCSS($columnWidth, $columnOffset);

        $this->setPreviousOffset($totalWidthInRow);

        return $htmlAndCss;
    }

    /**
     * @param int $width
     * @param int $rowOffset
     * @param int $offset
     * @param int $fill
     * @return string
     */
    protected function renderColumnPreviewRow($width, $rowOffset, $offset, $fill): string
    {
        $html = '<div class="column-layout-container">';

        $html .= '<div class="column-box-container">';
        $html .= $this->renderColumnPreviewBoxes($width, $rowOffset, $offset, $fill);
        $html .= '</div>';

        $html .= '<div class="column-info-container">';
        $html .= $this->renderAdditionalInformation();
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * @param int $width
     * @param int $rowOffset
     * @param int $offset
     * @param int $fill
     * @return string
     */
    protected function renderColumnPreviewBoxes($width, $rowOffset, $offset, $fill): string
    {
        $html = '';
        $widthLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.widths.label') . ': ' . $width;
        $offsetLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.offsets.label') . ': ' . $offset;

        for ($i = 1; $i <= $rowOffset; $i++) {
            $html .= '<span class="column-box"></span>';
        }

        for ($i = 1; $i <= $offset; $i++) {
            $html .= sprintf(
                '<span class="column-box offset" title="%s" data-toggle="tooltip" data-html="true" data-placement="top"></span>',
                $offsetLabel
            );
        }

        for ($i = 1; $i < $width; $i++) {
            $html .= sprintf(
                '<span class="column-box active" title="%s" data-toggle="tooltip" data-html="true" data-placement="top"></span>',
                $widthLabel
            );
        }
        $html .= sprintf(
            '<span class="column-box active last" title="%s" data-toggle="tooltip" data-html="true" data-placement="top"></span>',
            $widthLabel
        );

        for ($i = 1; $i <= $fill; $i++) {
            $html .= '<span class="column-box"></span>';
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function renderAdditionalInformation(): string
    {
        $html = '';

        $html .= sprintf(
            '<span class="cl-icon cl-icon-new-row %s" title="%s" data-toggle="tooltip" data-html="true" data-placement="bottom"></span>',
            (!$this->startNewRow ? 'hidden' : ''),
            $newRowLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.new_row.title')
        );
        $html .= sprintf(
            '<span class="cl-icon cl-icon-fullwidth %s" title="%s" data-toggle="tooltip" data-html="true" data-placement="bottom"></span>',
            (!$this->isFullwidthRow() ? 'hidden' : ''),
            $newRowLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.fullwidth.title')
        );

        return $html;
    }

    /**
     * @param $width
     * @param $offset
     * @return string
     */
    protected function generateFloatingCEColumnCSS($width, $offset): string
    {
        $css = sprintf(
            self::CSS_TEMPLATE_WITH_FLOATING,
            $this->row['uid'],
            (($width + $offset) / $this->maxColumns) * 100,
            $width,
            $offset
        );

        if (!$offset) {
            $css .= sprintf(
                '@media only screen and (min-width: 1024px) { #element-tt_content-%1$d::before { display: none; } }',
                $this->row['uid']
            );
        }

        return $css;
    }

    /**
     * @param $width
     * @param $offset
     * @return string
     */
    protected function generateNonFloatingCEColumnCSS($width, $offset): string
    {
        $css = sprintf(
            self::CSS_TEMPLATE_WITHOUT_FLOATING,
            $this->row['uid'],
            (($width + $offset) / $this->maxColumns) * 100
        );

        return $css;
    }

    /**
     * @return int
     */
    protected function calculateWidth(): int
    {
        $width = 0;
        $checkFieldNames = [
            'large_width',
            'medium_width',
            'small_width'
        ];

        // Check all breakpoints for a width value.
        foreach ($checkFieldNames as $fieldName) {
            if (isset($this->layoutConfiguration['sDEF'][$fieldName]) && $this->layoutConfiguration['sDEF'][$fieldName] > 0) {
                $width = (int)$this->layoutConfiguration['sDEF'][$fieldName];
                break;
            }
        }

        return ($width > 0 && $width <= $this->maxColumns) ? $width : $this->maxColumns;
    }

    /**
     * @return int
     */
    protected function calculateOffset(): int
    {
        $offset = 0;
        $checkFieldNames = [
            'large_offset',
            'medium_offset',
            'small_offset'
        ];

        // Check all breakpoints for a width value.
        foreach ($checkFieldNames as $fieldName) {
            if (isset($this->layoutConfiguration['sOffsets'][$fieldName]) && $this->layoutConfiguration['sOffsets'][$fieldName] > 0) {
                $offset = (int)$this->layoutConfiguration['sOffsets'][$fieldName];
                break;
            }
        }

        return ($offset > 0 && $offset < $this->maxColumns) ? $offset : 0;
    }

    /**
     * Checks if a new row should be started by configuration.
     * Is true when either new row is activated for content element or fullwidth row.
     *
     * @return bool
     */
    protected function shouldStartNewRow(): bool
    {
        return $this->layoutConfiguration['sDEF']['row_behaviour']
            || $this->isFullwidthRow()
            || $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$this->row['colPos']] === 0;
    }

    /**
     * @return bool
     */
    protected function isFullwidthRow(): bool
    {
        return (bool)$this->layoutConfiguration['sDEF']['row_fullwidth'];
    }

    /**
     * @return int
     */
    protected function getPreviousOffset(): int
    {
        return (int)$GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$this->row['colPos']];
    }

    /**
     * @param int $offset
     */
    protected function setPreviousOffset(int $offset)
    {
        $GLOBALS['TX_COLUMN_LAYOUT']['PageLayoutColumnOffset'][$this->row['colPos']] = $offset;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
