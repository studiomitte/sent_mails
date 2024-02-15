<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\Repository;

use StudioMitte\SentMails\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailRepository
{

    public function __construct(
        protected readonly Configuration $configuration
    )
    {
    }

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

    public function getMailsBySearch(string $searchTerm): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_sentmail_mail');
        return $queryBuilder
            ->select('is_sent')
            ->addSelectLiteral('count(*) as count')
            ->from('tx_sentmail_mail')
            ->where(
                $queryBuilder->expr()->gte('crdate', $queryBuilder->createNamedParameter((time() - $this->configuration->mailInformationMaxTime ), Connection::PARAM_INT)),
                $queryBuilder->expr()->like('message', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchTerm) . '%'))
            )
            ->groupBy('is_sent')
            ->executeQuery()->fetchAllAssociative();
    }


}
