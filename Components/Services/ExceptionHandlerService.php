<?php

namespace Sezzle\Components\Services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Shopware\Components\HttpClient\RequestException;
use Sezzle\Components\Exception\SezzleApiException;
use Sezzle\Components\ExceptionHandlerServiceInterface;
use Sezzle\SezzleBundle\Components\LoggerServiceInterface;
use Sezzle\SezzleBundle\Structs\ErrorResponse;

class ExceptionHandlerService implements ExceptionHandlerServiceInterface
{
    const DEFAULT_MESSAGE = 'An error occurred: ';
    const LOG_MESSAGE = 'Could not %s due to a communication failure';

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    /**
     * ExceptionHandlerService constructor.
     * @param LoggerServiceInterface $loggerService
     */
    public function __construct(LoggerServiceInterface $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $e, $currentAction)
    {
        $exceptionMessage = $e->getMessage();

        if (!($e instanceof RequestException)) {
            $this->loggerService->error(sprintf(self::LOG_MESSAGE, $currentAction), [
                'message' => $exceptionMessage,
            ]);

            return new SezzleApiException(
                $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $requestBody = $e->getBody();

        if (!$requestBody) {
            $clientException = $e->getPrevious();
            if ($clientException instanceof ClientException) {
                $requestBody = $clientException->getResponse()->getBody()->getContents();
            }
        }

        if (!$requestBody) {
            return new SezzleApiException(
                $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $requestBody = json_decode($requestBody, true);

        if (!is_array($requestBody)) {
            return new SezzleApiException(
                $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $errorStruct = ErrorResponse::fromArray($requestBody);

        if (!$errorStruct) {
            return new SezzleApiException(
                $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $message = self::DEFAULT_MESSAGE . $errorStruct->getMessage();

        return new SezzleApiException(
            $errorStruct->getLocation(),
            $message
        );
    }
}
