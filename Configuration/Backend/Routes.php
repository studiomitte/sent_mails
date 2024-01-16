<?php

return [
    'sentmail_preview' => [
        'path' => '/sentmail/preview',
        'target' => \StudioMitte\SentMails\Controller\MailAdministrationController::class . '::previewAction',
    ],
];