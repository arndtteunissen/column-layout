<?php
namespace Arndtteunissen\ColumnLayout\Form\FormDataProvider;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

/**
 * Saves the effectivePid from the (new) record in the database record
 *
 * @see DatabaseRowInitializeNew::setPid()
 */
class SaveEffectivePidInDatabaseRow implements FormDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function addData(array $result)
    {
        if (!is_array($result['databaseRow'])) {
            throw new \UnexpectedValueException(
                'databaseRow of table ' . $result['tableName'] . ' is not an array',
                1444431128
            );
        }

        $result['databaseRow']['_tx_column_layout_orig_pid'] = $result['effectivePid'];

        return $result;
    }
}
