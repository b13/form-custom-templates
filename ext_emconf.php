<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Custom Templates',
    'description' => 'Enable custom templates for emails sent using TYPO3\'s form framework email finishers',
    'category' => 'be',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'Jochen Roth',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'form' => '11.5.0-12.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
