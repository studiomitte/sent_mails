<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\EventListener;

use StudioMitte\SentMails\Repository\MailRepository;
use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterMailSentEventListener
{
    public function __construct(private MailRepository $mailRepository)
    {
    }

    public function __invoke(AfterMailerSentMessageEvent $event): void
    {
        /** @var Mailer $mailer */
        $mailer = $event->getMailer();
        $sentMessage = $mailer->getSentMessage();
        if (!$sentMessage) {
            return;
        }
        $originalMessage = $sentMessage->getOriginalMessage();
        $sentMailLogId = $originalMessage->getHeaders()->getHeaderBody('X-SentMail_ID');

        if (null === $sentMailLogId) {
            return;
        }

        $isReply = get_class($originalMessage) === RawMessage::class;
        $logRow = $this->mailRepository->getMailRow((int) $sentMailLogId);

        if ($logRow) {
            $this->getConnection()->update('tx_sentmail_mail',
                [
                    'message_id' => $isReply ? '' : $sentMessage->getMessageId(),
                    'debug' => $sentMessage->getDebug(),
                    'is_sent' => 1,
                ],
                [
                    'uid' => $logRow['uid'],
                ]
            );
        }
    }


    private function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_sentmail_mail');
    }
}
