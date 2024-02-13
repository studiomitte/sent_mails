<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\Repository;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailRepository
{

    public function getMails(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_sentmail_mail');
        return $queryBuilder
            ->select('*')
            ->from('tx_sentmail_mail')
            ->orderBy('crdate', 'desc')
            ->executeQuery()->fetchAllAssociative();
    }

    public function getMailRow(int $id): array
    {
        $row = BackendUtility::getRecord('tx_sentmail_mail', $id);
        return (array)$row;
    }


}
