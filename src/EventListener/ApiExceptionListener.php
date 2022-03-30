<?php

namespace App\EventListener;

use App\Exception\ApiException;
use App\Factory\NormalizerFactory;
use App\Responses\ApiResponse;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class ApiExceptionListener
{
    /**
     * @var NormalizerFactory
     */
    private NormalizerFactory $normalizerFactory;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private TranslatorInterface $translator;
    /**
     * @var string
     */
    private string $env;

    /**
     * ExceptionListener constructor.
     *
     * @param NormalizerFactory $normalizerFactory
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param string $env
     */
    public function __construct(
        NormalizerFactory $normalizerFactory,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        string $env
    ) {
        $this->normalizerFactory = $normalizerFactory;
        $this->logger = $logger;
        $this->env = $env;
        $this->translator = $translator;
    }

    /**
     * @param ExceptionEvent $event
     *
     * @throws ExceptionInterface
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $acceptable = $request->getAcceptableContentTypes();
        if (in_array('application/json', $acceptable, false)
            || in_array('*/*', $acceptable, false)) {
            $this->logger->warning(
                "KERNEL({$this->env}): " . $exception->getMessage() . ' ' .
                $exception->getTraceAsString()
            );
            $response = $this->createApiResponse($exception);
            $event->setResponse($response);
        }
    }

    /**
     * Creates the ApiResponse from any Exception.
     *
     * @param Throwable $exception
     *
     * @return ApiResponse
     *
     * @throws ExceptionInterface
     */
    private function createApiResponse(Throwable $exception)
    {
        $normalizer = $this->normalizerFactory->getNormalizer($exception);
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        try {
            $errors = $normalizer ? $normalizer->normalize($exception) : [];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $errors = [];
        }
        $data = null;
        $msg = $exception->getMessage();
        if ($exception instanceof ApiException) {
            $data = $exception->getData();
            $msg = $this->translator->trans($msg, $exception->getParams(), $exception->getLangFile());
        }
        return new ApiResponse(
            $data,
            $statusCode,
            $msg,
            $errors,
            [],
            $exception->getCode()
        );
    }
}