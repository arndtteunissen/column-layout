<?php
namespace Arndtteunissen\ColumnLayout\Domain\Model\Dto;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension Manager configuration
 */
class EmConfiguration
{
    /**
     * Fill the properties properly
     *
     * @param array $configuration em configuration
     */
    public function __construct(array $configuration)
    {
        foreach ($configuration as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @var string
     */
    protected $colPosListForDisable = '';

    /**
     * @return array
     */
    public function getColPosListForDisable(): array
    {
        if ($this->colPosListForDisable) {
            return GeneralUtility::intExplode(',', $this->colPosListForDisable);
        }

        return [];
    }
}
