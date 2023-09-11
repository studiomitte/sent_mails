<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use ZBateson\MailMimeParser\Message;

#[Controller]
class MailAdministrationController
{
    public function __construct(
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
    )
    {
    }

    public function overviewAction(ServerRequestInterface $request): ResponseInterface
    {
        $mails = $this->getMails();
        foreach ($mails as &$mail) {
            $this->enrichMail($mail);
        }
        $view = $this->moduleTemplateFactory->create($request);
        $view->assignMultiple([
            'mails' => $mails,
            'uriBuilder' => $this->uriBuilder,
        ]);

        $this->registerDocHeaderButtons($view, $request->getAttribute('normalizedParams')->getRequestUri());
        return $view->renderResponse('Overview.html');
    }

    protected function enrichMail(array &$mail): void
    {
        foreach (['sender', 'receiver', 'cc', 'bcc'] as $field) {
            $mail[$field] = $mail[$field] ? json_decode($mail[$field], true) : [];
        }
    }

    public function resendAction(ServerRequestInterface $request): ResponseInterface
    {
        $row = $this->getMailRow($request);
        if (!$row) {
            $this->addFlashMessage('No mail record found');
            return $this->getRedirectResponseToOverview();
        }
        $mailMess = GeneralUtility::makeInstance(Mailer::class);

        $message = unserialize($row['email_serialized']);
        $envelope = unserialize($row['envelope_original']);
        $mailMess->send($message, $envelope);

        $this->addFlashMessage(sprintf('Mail with id %d was resent', $row['uid']), '', ContextualFeedbackSeverity::OK);
        return $this->getRedirectResponseToOverview();
    }

    public function forwardAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        $mailRow = $this->getMailRow($request);
        if (!$mailRow) {
            $this->addFlashMessage('No mail record found');
            return $this->getRedirectResponseToOverview();
        }
        $this->enrichMail($mailRow);

        $view = $this->moduleTemplateFactory->create($request);
        $view->assignMultiple([
            'mail' => $mailRow,
            'params' => $params,
            'uriBuilder' => $this->uriBuilder,
        ]);

        if ($params['submit'] ?? false) {
            $mailMess = GeneralUtility::makeInstance(Mailer::class);

            /** @var Email $message */
            $message = unserialize($mailRow['email_serialized']);
            $envelope = unserialize($mailRow['envelope_original']);

            $message->to(new Address($params['toEmail'], $params['toName'] ?? ''));
            $message->subject($params['subject']);

            try {
                $mailMess->send($message, $envelope);
                $this->addFlashMessage(sprintf('Mail with id %d was resent', $mailRow['uid']), '', ContextualFeedbackSeverity::OK);
                return $this->getRedirectResponseToOverview();
            } catch (\Exception $e) {
                $view->assign('error', $e);
            }
        }

        return $view->renderResponse('Forward.html');
    }

    public function previewAction(ServerRequestInterface $request): ResponseInterface
    {
        $mail = $this->getMailRow($request);
        if (!$mail) {
            return new HtmlResponse('ERROR: No mail record found');
        }

        $message = Message::from($mail['original_message'], false);;

        switch ($request->getQueryParams()['type'] ?? '') {
            case 'plain':
                $content = $message->getTextContent();
                $content = '<pre>' . $content . '</pre>';
                break;
            case 'html':
                $content = $message->getHtmlContent();
                if (!$content) {
                    $content = '<div class="alert alert-warning">' . $this->getLanguageService()->sL('LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:preview.noHtmlContent') . '</div>';
                } else {
                    $content = '<iframe style="width:100%;height:100%;border:0" srcdoc="' . (htmlentities($content)) . '"></iframe>';
                }
                break;
            case 'debug':
                $content = '<pre>' . $mail['debug'] . '</pre>';
                break;
            case 'settings':
                $content = '<pre>' . DebuggerUtility::var_dump(json_decode($mail['settings'], true), '', 8, true, false, true) . '</pre>';
                break;
            default:
                return new HtmlResponse('ERROR: No type given');
        }

        return new HtmlResponse($content);
    }


    public function testAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        if (!isset($params['fromName'])) {
            $params['fromName'] = MailUtility::getSystemFromName();
        }
        if (!isset($params['fromEmail'])) {
            $params['fromEmail'] = MailUtility::getSystemFromAddress();
        }
        $view = $this->moduleTemplateFactory->create($request);
        $view->assign('params', $params);

        if ($params['submit'] ?? false) {
            try {

                $mailer = GeneralUtility::makeInstance(Mailer::class);
                $email = GeneralUtility::makeInstance(FluidEmail::class);
                $email->subject($params['subject']);
                $email->text($params['plain']);
                $email->html($params['html'] ?? '');
                $email->to(new Address($params['fromEmail'], $params['fromName']));
//            $email->cc(new Address('cc@example.org', 'Mail as CC'));
//            $email->bcc(...[
//                new Address('bcc1@example.org', 'Mail as BCC1'),
//                new Address('bcc2@example.org', 'Mail as BCC2'),
//            ]);
                $mailer->send($email);

                $this->addFlashMessage('Mail sent', '', ContextualFeedbackSeverity::OK);
                return new RedirectResponse($this->uriBuilder->buildUriFromRoute('sentmail_admin'));
            } catch (\Exception $e) {
                $view->assign('error', $e);
            }
        }

        return $view->renderResponse('Test.html');
    }

    private function addFlashMessage(string $message, string $title = '', $severity = ContextualFeedbackSeverity::WARNING): void
    {
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $severity, true);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    private function getMails(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mailsent_mail');
        return $queryBuilder
            ->select('*')
            ->from('tx_mailsent_mail')
            ->orderBy('crdate', 'desc')
            ->executeQuery()->fetchAllAssociative();
    }


    protected function registerDocHeaderButtons(ModuleTemplate $view, string $requestUri): void
    {
        $languageService = $this->getLanguageService();
        $buttonBar = $view->getDocHeaderComponent()->getButtonBar();

        // Create new
        $newRecordButton = $buttonBar->makeLinkButton()
            ->setHref((string)$this->uriBuilder->buildUriFromRoute(
                'sentmail_admin.test'
            ))
            ->setShowLabelText(true)
            ->setTitle($languageService->sL('LLL:EXT:sent_mails/Resources/Private/Language/locallang.xlf:module.sendTestMail'))
            ->setIcon($this->iconFactory->getIcon('actions-plus', Icon::SIZE_SMALL));
        $buttonBar->addButton($newRecordButton, ButtonBar::BUTTON_POSITION_LEFT, 10);
    }

    protected function getMailRow(ServerRequestInterface $request): array
    {
        $id = (int)($request->getQueryParams()['mail'] ?? 0);
        $row = BackendUtility::getRecord('tx_mailsent_mail', $id);
        return (array)$row;
    }

    protected function getRedirectResponseToOverview(): RedirectResponse
    {
        return new RedirectResponse($this->uriBuilder->buildUriFromRoute('sentmail_admin'));
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}