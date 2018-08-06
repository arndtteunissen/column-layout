<?php
namespace Arndtteunissen\ColumnLayout\Hook;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Utility\ColumnLayoutUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * A hook for adding column layout information to content elements in backend "Page" module.
 */
class ItemsProcFunc
{
    /**
     * @var ColumnLayoutUtility $columnLayoutUtility
     */
    protected $columnLayoutUtility;

    public function __construct()
    {
        $this->columnLayoutUtility = GeneralUtility::makeInstance(ColumnLayoutUtility::class);
    }

    /**
     * Itemsproc function to add additional layout classes to columns.
     *
     * @param array $config
     */
    public function additionalLayout(array &$config)
    {
        $pageId = $this->getPageId($config['flexParentDatabaseRow']['pid']);

        if ($pageId > 0) {
            $templateLayouts = $this->columnLayoutUtility->getAvailableLayouts($pageId);

            foreach ($templateLayouts as $layout) {
                $additionalLayout = [
                    htmlspecialchars($this->getLanguageService()->sL($layout[0])),
                    $layout[1]
                ];
                $config['items'][] = $additionalLayout;
            }
        }
    }

    /**
     * Get page id, if negative, then it is a "after record"
     *
     * @param int $pid
     * @return int
     */
    protected function getPageId($pid): int
    {
        $pid = (int)$pid;

        if ($pid > 0) {
            return $pid;
        }

        $row = BackendUtility::getRecord('tt_content', abs($pid), 'uid,pid');

        return (int)$row['pid'];
    }

    /**
     * Returns LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
