<?php

return [
    'module-sentmails' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:sent_mails/Resources/Public/Icons/Module.svg',
    ],
    'sentmails-type-text' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:sent_mails/Resources/Public/Icons/type_text.svg',
    ],
    'sentmails-type-html' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:sent_mails/Resources/Public/Icons/type_html.svg',
    ],
    'sentmails-status-ok' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:sent_mails/Resources/Public/Icons/status_ok.svg',
    ],
    'sentmails-status-error' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:sent_mails/Resources/Public/Icons/status_error.svg',
    ],
];
