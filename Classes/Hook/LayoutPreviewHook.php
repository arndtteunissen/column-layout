<?php
namespace Arndtteunissen\ColumnLayout\Hook;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Backend\ColumnRenderer;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawFooterHookInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A hook for adding column layout information to content elements in backend "Page" module.
 */
class LayoutPreviewHook implements PageLayoutViewDrawFooterHookInterface, SingletonInterface
{
    /**
     * @var bool
     */
    protected $activateElementFloating = false;

    /**
     * @var bool
     */
    protected $isInitialized = false;

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
    public function injectStyleSheets(array $params, PageLayoutController $ref): string
    {
        $ref->getModuleTemplate()->getPageRenderer()->addCssFile('EXT:column_layout/Resources/Public/Css/column_layout.css');

        if ($this->activateElementFloating) {
            $ref->getModuleTemplate()->getPageRenderer()->addCssFile('EXT:column_layout/Resources/Public/Css/activate_floating.css');
        }

        return '';
    }

    /**
     * {@inheritdoc}
     * @param array $info
     */
    public function preProcess(PageLayoutView &$parentObject, &$info, array &$row)
    {
        $this->init($parentObject);

        if ($this->skipRendering($parentObject, $row)) {
            return;
        }

        $renderer = GeneralUtility::makeInstance(ColumnRenderer::class, $row);

        // Do not float the elements when hidden records are shown.
        $renderer->setActivateElementFloating($this->activateElementFloating);

        $info[] = $renderer->renderSingleColumn();

        return;
    }

    /**
     * Checks if the rendering of the column markup should be skipped.
     *
     * @param PageLayoutView $parentObject
     * @return bool
     */
    protected function skipRendering(PageLayoutView &$parentObject, array $row): bool
    {
        $tsConf = $parentObject->modTSconfig['column_layout'] ?? [];

        return (
            $row['hidden'] ||
            $tsConf['hidePreview'] ?? false                     // Hidden via page TSConfig
            || empty($row['tx_column_layout_column_config'])    // Not set
            || $tsConf['disabled'] ?? false                     // Hidden for this element
        );
    }

    /**
     * Checks if elements should float.
     * Floating is active, when no hidden elements are shown.
     *
     * @param PageLayoutView $pageLayoutView
     * @return bool
     */
    protected function isElementFloatingActive(PageLayoutView $pageLayoutView): bool
    {
        $showHiddenRecords = (bool)$pageLayoutView->tt_contentConfig['showHidden'];

        if ($showHiddenRecords) {
            // Check if there are any hidden records on that page.
            $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($pageLayoutView->table)
                ->getExpressionBuilder();
            $constraints = [
                $expressionBuilder->eq($pageLayoutView->table . '.hidden', 1)
            ];
            $queryBuilder = $pageLayoutView->getQueryBuilder('tt_content', $pageLayoutView->id, $constraints, ['hidden']);
            $result = $queryBuilder->execute();

            if ($result->rowCount() > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param PageLayoutView $pageLayoutView
     */
    protected function init(PageLayoutView $pageLayoutView)
    {
        if ($this->isInitialized) {
            return;
        }

        $this->activateElementFloating = $this->isElementFloatingActive($pageLayoutView);
        $this->isInitialized = true;
    }
}
