<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\EventListener;

use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterMailSentEventListener
{

    public function __invoke(AfterMailerSentMessageEvent $event): void
    {
        /** @var Mailer $mailer */
        $mailer = $event->getMailer();
        $transport = $mailer->getTransport();
        $sentMessage = $mailer->getSentMessage();
        if (!$sentMessage) {
            return;
        }
        $originalMessage = $sentMessage->getOriginalMessage();
        $sentMailLogCustomId = $originalMessage->getHeaders()->getHeaderBody('X-SentMail_Custom');
        $sentMailLogId = $originalMessage->getHeaders()->getHeaderBody('X-SentMail_ID');

        $isReply = get_class($originalMessage) === RawMessage::class;
        $logRow = $this->getLogRecord((int)$sentMailLogId, $sentMailLogCustomId);

        $connection = $this->getConnection();
        if ($logRow) {
            $connection->update('tx_sentmail_mail',
                [
                    'message_id' => $isReply ? '' : $sentMessage->getMessageId(),
                    'debug' => $sentMessage->getDebug(),
                 #   'envelope_original' => serialize($sentMessage->getEnvelope()),
                    'is_sent' => 1,
                ],
                [
                    'uid' => $logRow['uid'],
                ]
            );
        }
        return;
    }

    protected function getLogRecord(int $id, string $customId): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        return (array)$queryBuilder
            ->select('*')
            ->from('tx_sentmail_mail')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
    }

    private function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_sentmail_mail');
    }
}
