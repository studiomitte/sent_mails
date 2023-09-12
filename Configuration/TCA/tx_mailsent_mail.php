<?php

return [
    'ctrl' => [
        'title' => 'Mail log',
        'label' => 'crdate',
        'label_alt' => 'subject',
        'label_alt_force' => true,
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
        ],
        'iconfile' => 'EXT:sent_mails/Resources/Public/Icons/tx_sentmail_mail.svg'
    ],
    'types' => [
        '1' => ['showitem' => 'crdate,subject,
                    --palette--;;paletteFrom,
                    --palette--;;paletteTo,
                    debug,message_id,message
        '],
    ],
    'palettes' => [
        'paletteFrom' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:from',
            'showitem' => 'from_name, from_email',
        ],
        'paletteTo' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:to',
            'showitem' => 'to_name, to_email',
        ],
    ],
    'columns' => [
        'crdate' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:from_name',
            'config' => [
                'type' => 'datetime',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'from_name' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:from_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'to_name' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:to_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'from_email' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:from_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'to_email' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:to_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'subject' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:subject',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'debug' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:debug',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'message_id' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:message_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'message' => [
            'label' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:message',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
    ],
];