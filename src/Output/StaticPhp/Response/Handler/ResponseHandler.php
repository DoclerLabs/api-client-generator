<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler;

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
