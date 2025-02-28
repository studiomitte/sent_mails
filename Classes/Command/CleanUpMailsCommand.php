<?php

declare(strict_types=1);

namespace StudioMitte\SentMails\Command;

use StudioMitte\SentMails\Repository\MailRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'sentmails:cleanup',
    description: 'Deletes sent mails that are older than a specified number of days from the database'
)]
class CleanUpMailsCommand extends Command
{
    private const DAYS_THRESHOLD_OPTION_NAME = 'days-threshold';

    private const DRY_RUN_OPTION_NAME = 'dry-run';

    private const IGNORE_SENT_STATUS_OPTION_NAME = 'ignore-sent-status';

    public function __construct(
        private MailRepository $mailRepository,
        private Context $context
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                self::DAYS_THRESHOLD_OPTION_NAME,
                '',
                InputOption::VALUE_OPTIONAL,
                'Mails older than the specified number of days will be removed from the database',
                7
            )
            ->addOption(
                self::IGNORE_SENT_STATUS_OPTION_NAME,
                null,
                InputOption::VALUE_NONE,
                'If set, all emails older than the specified number of days will be removed, even if they haven\'t been sent'
            )
            ->addOption(
                self::DRY_RUN_OPTION_NAME,
                null,
                InputOption::VALUE_NONE,
                'If set, items to be removed are collected and shown, but not removed '
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $days = (int) $input->getOption(self::DAYS_THRESHOLD_OPTION_NAME);
        $ignoreSentStatus = (bool) $input->getOption(self::IGNORE_SENT_STATUS_OPTION_NAME);
        $dryRun = (bool) $input->getOption(self::DRY_RUN_OPTION_NAME);
        Assert::greaterThan($days, 0);

        /** @var \DateTimeImmutable $now */
        $now = $this->context->getPropertyFromAspect('date', 'full');
        $deleteBeforeTimestamp = ($now->modify('-' . $days . 'DAY'))->getTimestamp();

        $symfonyStyle->info('Get mails sent before ' . $deleteBeforeTimestamp);

        $mails = $this->mailRepository->getMailsCreatedBefore($deleteBeforeTimestamp, $ignoreSentStatus);

        if (0 === \count($mails)) {
            $symfonyStyle->info('No emails found for deletion');

            return Command::SUCCESS;
        }

        $symfonyStyle->info(\sprintf('Found %d mails', \count($mails)));

        $obsoleteMailIds = array_column($mails, 'uid');

        if ($dryRun) {
            $symfonyStyle->info('The following mails should be removed. (Dry run):');
            $symfonyStyle->table(['uid'], array_map(fn ($uid) => ['uid' => $uid], $obsoleteMailIds));

            return Command::SUCCESS;
        }

        $this->mailRepository->deleteByUids($obsoleteMailIds);

        return Command::SUCCESS;
    }
}
