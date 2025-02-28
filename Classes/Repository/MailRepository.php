<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\Repository;

use StudioMitte\SentMails\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class MailRepository
{
    private const TABLE_NAME = 'tx_sentmail_mail';

    public function __construct(
        protected readonly Configuration $configuration,
        protected readonly ConnectionPool $connectionPool
    ) {
    }

    public function getMails(): array
    {
        return $this->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('crdate', 'desc')
            ->executeQuery()->fetchAllAssociative();
    }

    public function getMailRow(int $id): array
    {
        $row = BackendUtility::getRecord(self::TABLE_NAME, $id);

        return (array) $row;
    }

    public function getMailsBySearch(string $searchTerm): array
    {
        $queryBuilder = $this->createQueryBuilder();

        return $queryBuilder
            ->select('is_sent')
            ->addSelectLiteral('count(*) as count')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->gte('crdate', $queryBuilder->createNamedParameter((time() - $this->configuration->mailInformationMaxTime), Connection::PARAM_INT)),
                $queryBuilder->expr()->like('message', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchTerm) . '%'))
            )
            ->groupBy('is_sent')
            ->executeQuery()->fetchAllAssociative();
    }

    public function getMailsCreatedBefore(int $timestamp, bool $ignoreSentStatus = false): array
    {
        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('crdate < :timestamp')
            ->setParameter('timestamp', $timestamp);

        if (true !== $ignoreSentStatus) {
            $queryBuilder->andWhere('is_sent = 1');
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function deleteByUids(array $mailUids): void
    {
        $this->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('uid IN(:uids)')
            ->setParameter('uids', $mailUids, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
    }
}
