<?php

return [
    'sentmail_admin' => [
        'parent' => 'site',
        'access' => 'user',
        'path' => '/module/sentmail',
        'iconIdentifier' => 'module-sentmails',
        'labels' => [
            'title' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:module.title',
            'description' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:module.description',
            'shortDescription' => 'LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:module.shortDescription',
        ],
        'routes' => [
            '_default' => [
                'target' => \StudioMitte\SentMails\Controller\MailAdministrationController::class . '::overviewAction',
            ],
            'resend' => [
                'target' => \StudioMitte\SentMails\Controller\MailAdministrationController::class . '::resendAction',
            ],
            'test' => [
                'target' => \StudioMitte\SentMails\Controller\MailAdministrationController::class . '::testAction',
            ],
            'forward' => [
                'target' => \StudioMitte\SentMails\Controller\MailAdministrationController::class . '::forwardAction',
            ],
        ],
    ],

];
