<?php
declare(strict_types=1);

namespace StudioMitte\SentMails\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StudioMitte\SentMails\Configuration;
use StudioMitte\SentMails\Repository\MailRepository;
use TYPO3\CMS\Core\Http\JsonResponse;

class MailInformation implements MiddlewareInterface
{

    public function __construct(
        protected readonly MailRepository $mailRepository,
        protected readonly Configuration $configuration
    )
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isValidRequest($request)) {
            return $handler->handle($request);
        }

        $response = new JsonResponse();
        if (!$this->validateBasicAuth($request)) {
            $response = $response->withAddedHeader('WWW-Authenticate', 'Basic realm="Access check"');
        }

        try {
            $searchParam = $this->getSearchParam($request);
            $response->setPayload($this->mailRepository->getMailsBySearch($searchParam));
        } catch (\Exception $e) {
            $response = $response->withStatus(400);
            $response->setPayload(['error' => $e->getMessage()]);
        }
        return $response;
    }

    protected function getSearchParam(ServerRequestInterface $request): string
    {
        $searchParams = trim($request->getQueryParams()['search'] ?? '');
        if (mb_strlen($searchParams) < 10) {
            throw new \InvalidArgumentException('Search parameter must be at least 10 characters long', 1627980733);
        }
        return $searchParams;
    }

    protected function isValidRequest(ServerRequestInterface $request): bool
    {
        if (!$request->getUri()) {
            return false;
        }
        $uri = $request->getUri();

        return $uri->getPath() === '/api/mailinformation';
    }

    protected function validateBasicAuth(ServerRequestInterface $request): bool
    {
        $username = $request->getServerParams()['PHP_AUTH_USER'] ?? false;
        $password = $request->getServerParams()['PHP_AUTH_PW'] ?? false;

        return
            $this->configuration->mailAPIUsername &&
            $this->configuration->mailAPIPassword &&
            $username === $this->configuration->mailAPIUsername &&
            $password === $this->configuration->mailAPIPassword;
    }

}
