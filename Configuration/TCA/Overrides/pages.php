<?php

declare(strict_types=1);

$fields = [
    'tx_aigelb_indexpage' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_indexpage',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_aigelb_promptrequirement' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_promptrequirement',
        'config' => [
            'type' => 'text',
        ],
    ],
    'tx_aigelb_knowledgebase' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_knowledgebase',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_aigelb_language' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_language',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'tx_aigelb_lastupdated' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_lastupdated',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'readOnly' => true,
        ],
    ],
    'tx_aigelb_knowledgeid' => [
        'exclude' => true,
        'label' => 'LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:tx_aigelb_knowledgeid',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'readOnly' => true,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $fields
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:aigelb/Resources/Private/Language/locallang_db.xlf:aigelb_tabheader, tx_aigelb_indexpage, tx_aigelb_promptrequirement, tx_aigelb_knowledgebase, tx_aigelb_language, tx_aigelb_lastupdated, tx_aigelb_knowledgeid',
    '',
    'after:parent'
);
