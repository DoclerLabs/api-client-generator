<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\BadRequestResponseException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\ForbiddenResponseException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\NotFoundResponseException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\PaymentRequiredResponseException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\ResponseExceptionFactory;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\UnauthorizedResponseException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

class ResponseHandler implements ResponseHandlerInterface
{
    /** @var ResponseExceptionFactory */
    private $responseExceptionsFactory;

    public function __construct()
    {
        $this->responseExceptionsFactory = new ResponseExceptionFactory();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Response
     *
     * @throws BadRequestResponseException
     * @throws UnauthorizedResponseException
     * @throws PaymentRequiredResponseException
     * @throws ForbiddenResponseException
     * @throws NotFoundResponseException
     * @throws UnexpectedResponseException
     * @throws JsonException
     */
    public function handle(ResponseInterface $response): Response
    {
        $statusCode          = $response->getStatusCode();
        $body                = $response->getBody();
        $headers             = $response->getHeaders();
        $isResponseBodyEmpty = $this->isResponseBodyEmpty($body);
        $responseBody        = '';

        if (!$isResponseBodyEmpty) {
            $responseBody = (string)$body;
        }

        if ($statusCode >= 200 && $statusCode < 300) {
            if ($isResponseBodyEmpty) {
                return $response;
            }

            $response->withBody();

            return $response;
        }

        throw $this->responseExceptionsFactory->create($statusCode, $responseBody);
    }

    private function isResponseBodyEmpty(StreamInterface $responseBody = null): bool
    {
        return $responseBody === null || (int)$responseBody->getSize() === 0;
    }
}
