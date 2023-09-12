<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\EventListener;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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

//        DebuggerUtility::var_dump($isReply);
//        DebuggerUtility::var_dump($originalMessage, 'original');
//        DebuggerUtility::var_dump($sentMailLogCustomId, '$sentMailLogCustomId');
//        DebuggerUtility::var_dump($sentMailLogId, '$sentMailLogId');
//        DebuggerUtility::var_dump($sentMessage->getHeaders(), 'sentXXX');

        $logRow = $this->getLogRecord((int)$sentMailLogId, $sentMailLogCustomId);
//        DebuggerUtility::var_dump($logRow, 'logrow');

        $connection = $this->getConnection();
        if ($logRow) {
            $connection->update('tx_sentmail_mail',
                [
                    'message_id' => $isReply ? '' : $sentMessage->getMessageId(),
                    'debug' => $sentMessage->getDebug(),
                    'envelope_original' => serialize($sentMessage->getEnvelope()),
                    'is_sent' => 1,
                ],
                [
                    'uid' => $logRow['uid'],
                ]
            );
        }
        return;
        DebuggerUtility::var_dump($logRow);
        die;

        DebuggerUtility::var_dump($originalMessage->getHeaders());
        die;

        $envelope = $sentMessage->getEnvelope();

//        DebuggerUtility::var_dump($sentMessage, 'sent');
//
//        die;
        $connection = $this->getConnection();
        $connection->insert('tx_sentmail_mail',
//        DebuggerUtility::var_dump(
            [
                'pid' => 11,
                'crdate' => time(),
                'subject' => $isReply ? '' : $originalMessage->getHeaders()->getHeaderBody('Subject'),
                'from_name' => $envelope->getSender()->getName(),
                'from_email' => $envelope->getSender()->getAddress(),
                'to_name' => $envelope->getRecipients()[0]->getName(),
                'to_email' => $envelope->getRecipients()[0]->getAddress(),
                'debug' => $sentMessage->getDebug(),
                'message_id' => $isReply ? '' : $sentMessage->getMessageId(),
                'message' => $sentMessage->toString(),
                'original_message' => $originalMessage->toString(),
                'envelope_original' => serialize($envelope),
                'email_serialized' => $originalMessage instanceof Email ? serialize($originalMessage) : '',
            ]
        );

        // todos: settings, attachments, cc, bcc,
    }

    protected function getLogRecord(int $id, string $customId): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        return (array)$queryBuilder
            ->select('*')
            ->from('tx_sentmail_mail')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)),
//                $queryBuilder->expr()->eq('message_id', $queryBuilder->createNamedParameter($customId)),
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