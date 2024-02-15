<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\EventListener;


use StudioMitte\SentMails\Configuration;
use Symfony\Component\Mailer\Event\MessageEvent;

class MailMessageListener
{

    public function __construct(
        protected Configuration $configuration)
    {
    }

    public function __invoke(MessageEvent $event): void
    {
        $message = $event->getMessage();

        $messageAsString = $message->toString();
        foreach ($this->configuration->rejectBySearchTerms as $searchTerm) {
            if (preg_match(sprintf('/%s/i', $searchTerm), $messageAsString)) {
                $event->reject();
                return;
            }
        }
    }
}
