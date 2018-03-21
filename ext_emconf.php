<?php

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Column Layout',
    'description' => 'Adds column configuration to fluid styled content elements',
    'version' => '0.3.1',
    'category' => 'fe',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99'
        ]
    ],
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'author' => 'Arndtteunissen',
    'author_email' => 'dev@arndtteunissen.de',
    'author_company' => 'Arndtteunissen',
    'autoload' =>[
        'psr-4' =>[
            'Arndtteunissen\\ColumnLayout\\' => 'Classes/'
        ]
    ]
];
