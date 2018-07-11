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
    #element-tt_content-%d { width: %d%%; } 
    #element-tt_content-%1$d > .t3-page-ce-dragitem { flex: %d; } 
    #element-tt_content-%1$d::before { flex: %d; content: '%4$s'; }
}
CSS;

    const CSS_TEMPLATE_WITHOUT_FLOATING = <<<'CSS'
@media only screen and (min-width: 1024px) {
    #element-tt_content-%d { width: %d%%; }
}
CSS;

    /**
     * When activated, elements will float.
     *
     * @var bool
     */
    protected $activateElementFloating = false;

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
     * @param bool $activateElementFloating
     */
    public function setActivateElementFloating(bool $activateElementFloating)
    {
        $this->activateElementFloating = $activateElementFloating;
    }

    /**
     * @return string The additional html markup
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function renderSingleColumn(): string
    {
        $html = [];
        $columnWidth = $this->calculateWidth();
        $columnOffset = $this->calculateOffset();

        $totalOffsetInRow = $this->getPreviousOffset() + $columnOffset;
        $totalWidthInRow = $totalOffsetInRow + $columnWidth;

        if ($this->shouldStartNewRow() || $totalWidthInRow > $this->maxColumns) {
            $this->startNewRow = true;
            $this->setPreviousOffset($columnOffset);
            $totalOffsetInRow = $columnOffset;
            $totalWidthInRow = $columnOffset + $columnWidth;
        }

        if ($this->isFullwidthRow()) {
            $this->startNewRow = true;
            $this->setPreviousOffset(0);
            $columnOffset = 0;
            $totalOffsetInRow = 0;
            $columnWidth = $this->maxColumns;
            $totalWidthInRow = $this->maxColumns;
        }

        $html[] = $this->renderColumnPreviewRow($columnWidth, $totalOffsetInRow, $this->maxColumns - $totalWidthInRow);
        $html[] = $this->generateGridCss($columnWidth, $columnOffset);

        $this->setPreviousOffset($totalWidthInRow);

        return implode("\n", $html);
    }

    /**
     * @param int $width
     * @param int $offset
     * @param int $fill
     * @return string
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function renderColumnPreviewRow($width, $offset, $fill): string
    {
        $html = '<div class="column-layout-container">';

        $html .= $this->renderColumnPreviewBoxes($width, $offset, $fill);

        $widthLabel = $this->getLanguageService()->sL(ColumnLayoutUtility::getColumnLayoutSettings($this->row['pid'])['types.']['widths.']['label']);
        $offsetLabel = $this->getLanguageService()->sL(ColumnLayoutUtility::getColumnLayoutSettings($this->row['pid'])['types.']['offsets.']['label']);
        $newRowLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.new_row.label');
        $fullwidthRowLabel = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.fullwidth_row.label');
        $yes = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.yes');
        $no = $this->getLanguageService()->sL('LLL:EXT:column_layout/Resources/Private/Language/locallang_be.xlf:column_layout.no');

        $html .= '<div class="column-info-container">';
        $html .= sprintf('<span>%s: %d</span>', $widthLabel, $width);
        $html .= ' ' . sprintf('<span>%s: %d</span>', $offsetLabel, $offset);
        $html .= '<br />';
        $html .= sprintf('<span>%s: %s</span>', $newRowLabel, $this->startNewRow ? $yes : $no );
        $html .= '<br />';
        $html .= sprintf('<span>%s: %s</span>', $fullwidthRowLabel, $this->isFullwidthRow() ? $yes : $no );
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
     * @param int $width
     * @param int $offset
     * @return string
     */
    protected function generateGridCss($width, $offset): string
    {
        return sprintf(
            '<style type="text/css">%s</style>',
            $this->activateElementFloating ? $this->generateFloatingCEColumnCSS($width, $offset) : $this->generateNonFloatingCEColumnCSS($width, $offset)
        );
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
