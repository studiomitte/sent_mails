<?php

return [
    'frontend' => [
        'typo3/sent-mails/mailinformation' => [
            'target' => \StudioMitte\SentMails\Middleware\MailInformation::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
            'after' => [
            ],
        ],
    ],
];
