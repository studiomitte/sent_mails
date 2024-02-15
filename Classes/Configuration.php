<?php

declare(strict_types=1);

namespace StudioMitte\SentMails;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{

    /** @var string[] */
    public array $rejectBySearchTerms = [];

    public int $mailInformationMaxTime = 60;

    public string $mailAPIUsername = '';
    public string $mailAPIPassword = '';

    public function __construct()
    {
        try {
            $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sent_mails');

            $this->rejectBySearchTerms = GeneralUtility::trimExplode('|', $settings['rejectBySearchTerms'] ?? '', true);
            $this->mailAPIUsername = $settings['mailAPIUsername'] ?? '';
            $this->mailAPIPassword = $settings['mailAPIPassword'] ?? '';
        } catch (\Exception $e) {
            // do nothing
        }
    }

}
