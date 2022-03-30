<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use App\Responses\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class JWTListener
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;
    /**
     * @var UserRepository
     */
    private UserRepository $repository;

    /**
     * @var string
     */
    private string $issuer;

    /**
     * JWTListener constructor.
     *
     * @param TranslatorInterface $translator
     * @param UserRepository $repository
     * @param string $jwtIssuer
     */
    public function __construct(TranslatorInterface $translator, UserRepository $repository, string $jwtIssuer)
    {
        $this->translator = $translator;
        $this->repository = $repository;
        $this->issuer = $jwtIssuer;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
        $payload = $event->getData();
        $payload['iss'] = $this->issuer;
        $event->setData($payload);
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $response = new ApiResponse(
            null,
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('jwt.is_invalid', [], 'validators')
        );

        $event->setResponse($response);
    }

    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $response = new ApiResponse(
            null,
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('jwt.is_expired', [], 'validators')
        );
        $event->setResponse($response);
    }

    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $response = new ApiResponse(
            null,
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('jwt.is_not_found', [], 'validators')
        );
        $event->setResponse($response);
    }

    /**
     * @param JWTDecodedEvent $event
     */
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        if (!$this->canLogin($event)) {
            $event->markAsInvalid();
        }
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return bool
     */
    private function canLogin(JWTDecodedEvent $event): bool
    {
        $payload = $event->getPayload();
        $iss = $payload['iss'] ?? '';
        if (array_key_exists('iss', $payload) && $iss !== $this->issuer) {
            return false;
        }
        $user = $this->repository->findOneBy(['id' => $payload['id'] ?? '0']);
        return $user !== null;
    }
}