<?php

return [
    'frontend' => [
        'typo3/theme/logger' => [
            'target' => \StudioMitte\SentMails\Middleware\MailInformation::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
            'after' => [
            ],
        ],
    ],
];
