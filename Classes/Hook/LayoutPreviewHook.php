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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A hook for adding column layout information to content elements in backend "Page" module.
 */
class LayoutPreviewHook implements PageLayoutViewDrawFooterHookInterface, SingletonInterface
{
    /**
     * @var array
     */
    protected $inlineStyles = [];

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
    public function injectStylesAndScripts(array $params, PageLayoutController $ref): string
    {
        $jsCallbackFunction = null;

        // The translation view is shown. Disable floating elements for that view.
        if ($ref->MOD_SETTINGS['function'] === '2') {
            $jsCallbackFunction = 'function(ColumnLayout) {
                ColumnLayout.settings.isTranslationView = true;
            }';
        }

        $ref->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/ColumnLayout/ColumnLayout', $jsCallbackFunction);
        $ref->getModuleTemplate()->getPageRenderer()->addCssFile('EXT:column_layout/Resources/Public/Css/column_layout.css');
        $ref->getModuleTemplate()->getPageRenderer()->addCssInlineBlock('column-layout', implode("\n", $this->inlineStyles), true);

        return '';
    }

    /**
     * {@inheritdoc}
     * @param array $info
     */
    public function preProcess(PageLayoutView &$parentObject, &$info, array &$row)
    {
        if ($this->skipRendering($parentObject, $row)) {
            return;
        }

        /** @var ColumnRenderer $renderer */
        $renderer = GeneralUtility::makeInstance(ColumnRenderer::class, $row);

        if (!$renderer->skipRendering()) {
            list($html, $css) = $renderer->renderSingleColumn();

            $info[] = $html;
            $this->inlineStyles[] = $css;
        }

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
            $tsConf['hidePreview'] ?? false                     // Hidden via page TSConfig
            || empty($row['tx_column_layout_column_config'])    // Not set
            || $tsConf['disabled'] ?? false                     // Hidden for this element
        );
    }
}
