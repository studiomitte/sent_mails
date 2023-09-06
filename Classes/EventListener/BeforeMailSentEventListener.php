<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\EventListener;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\Event\BeforeMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class BeforeMailSentEventListener
{

    private array $blindedConfiguration = [
        'dsn' => '******',
        'transport_smtp_encrypt' => '******',
        'transport_smtp_password' => '******',
        'transport_smtp_server' => '******',
        'transport_smtp_username' => '******',
    ];

    public function __invoke(BeforeMailerSentMessageEvent $event): void
    {
        /** @var Mailer $mailer */
        $mailer = $event->getMailer();
        $transport = $mailer->getTransport();
        /** @var Email $sentMessage */
        $sentMessage = $event->getMessage();
        /** @var Email $originalMessage */
        $originalMessage = $event->getMessage();

        $customId = StringUtility::getUniqueId('mail_');

        $sentMessage->getHeaders()->addTextHeader('X-SentMail_Custom', $customId);

//        $originalMessage = $sentMessage->getOriginalMessage();
        $isReply = get_class($originalMessage) === RawMessage::class;
        $envelope = $event->getEnvelope();
//        DebuggerUtility::var_dump($isReply, '$isReply');
//        DebuggerUtility::var_dump($envelope, 'envelope');
//
//        die;
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_mailsent_mail');
        $connection->insert('tx_mailsent_mail',
            [
                'crdate' => time(),
                'subject' => $isReply ? '' : $originalMessage->getHeaders()->getHeaderBody('Subject'),
                'from_name' => $envelope ? $envelope->getSender()->getName() : $originalMessage->getFrom()[0]->getName(),
                'from_email' => $envelope ? $envelope->getSender()->getAddress() : $originalMessage->getFrom()[0]->getAddress(),
                'to_name' => $envelope ? $envelope->getRecipients()[0]->getName() : $originalMessage->getTo()[0]->getName(),
                'to_email' => $envelope ? $envelope->getRecipients()[0]->getAddress() : $originalMessage->getTo()[0]->getAddress(),
                'debug' => '',
                'message_id' => $customId,
                'message' => $sentMessage->toString(),
                'original_message' => $originalMessage->toString(),
                'envelope_original' => serialize($envelope),
                'internal_id' => $customId,
                'email_serialized' => $originalMessage instanceof Email ? serialize($originalMessage) : '',
                'settings' => json_encode($this->getSettings()),
            ]
        );
        $sentMessage->getHeaders()->remove('X-SentMail_ID');
        $sentMessage->getHeaders()->addTextHeader('X-SentMail_ID', $connection->lastInsertId('tx_mailsent_mail'));
    }

    protected function getSettings(): array
    {
        $settings = (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL'];

        ArrayUtility::mergeRecursiveWithOverrule(
            $settings,
            ArrayUtility::intersectRecursive($settings, $this->blindedConfiguration)
        );

        ArrayUtility::naturalKeySortRecursive($settings);
        return $settings;
    }
}