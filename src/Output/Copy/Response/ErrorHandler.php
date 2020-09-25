<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Response;

use DoclerLabs\ApiClientException\Factory\ResponseExceptionFactory;
use Psr\Http\Message\ResponseInterface;

class ErrorHandler
{
    /** @var ResponseExceptionFactory */
    private $responseExceptionFactory;

    public function __construct(ResponseExceptionFactory $exceptionFactory)
    {
        $this->responseExceptionFactory = $exceptionFactory;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function handle(ResponseInterface $response): ResponseInterface
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return $response;
        }

        throw $this->responseExceptionFactory->create(
            sprintf('Server replied with a non-200 status code: %s', $response->getStatusCode()),
            $response
        );
    }
}
