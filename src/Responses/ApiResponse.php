<?php

namespace App\Responses;

use App\Factory\JmsFactory;
use InvalidArgumentException;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResponse.
 */
class ApiResponse extends JsonResponse
{
    /**
     * @var ?int
     */
    private $code;

    /**
     * @var int
     */
    private int $status;

    /**
     * @var array
     */
    private array $groups = [];

    /**
     * @var ExclusionStrategyInterface|null
     */
    private ?ExclusionStrategyInterface $exclusionStrategy = null;

    /**
     * ApiResponse constructor.
     *
     * @param null $data
     * @param int $status
     * @param string $message
     * @param array $errors
     * @param array $headers
     * @param int|null $code
     * @param array $groups
     * @param ExclusionStrategyInterface|null $exclusionStrategy
     */
    public function __construct(
        $data = null,
        int $status = Response::HTTP_OK,
        string $message = '',
        array $errors = [],
        array $headers = [],
        ?int $code = null,
        array $groups = ['api'],
        ?ExclusionStrategyInterface $exclusionStrategy = null
    ) {
        $this->code = $code;
        $this->status = $status;
        $this->groups = $groups;
        $this->exclusionStrategy = $exclusionStrategy;
        parent::__construct($this->format($message, $data, $errors), $status, $headers);
    }

    /**
     * Format data.
     *
     * @param string $message
     * @param array  $data
     * @param array  $errors
     *
     * @return array[]
     *
     * @psalm-return array{data?: array, error?: array{code: int, message: string, errors?: array}}
     */
    private function format(string $message, $data = [], array $errors = []): array
    {
        $response = [
            'data' => $data
        ];
        if ($message) {
            if ($this->code) {
                $response['error']['code'] = $this->code;
            } else {
                $response['error']['code'] = $this->status;
            }
            $response['error']['message'] = $message;
        }

        if ($errors) {
            $response['error']['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setData($data = []): self
    {
        $serializer = JmsFactory::create();

        if (is_string($data)) {
            return $this->setJson($data);
        }
        $ctx = SerializationContext::create()
            ->setGroups($this->groups)
            ->setSerializeNull(true);
        if ($this->exclusionStrategy) {
            $ctx = $ctx->addExclusionStrategy($this->exclusionStrategy);
        }
        return $this->setJson($serializer->serialize($data, 'json', $ctx));
    }
}
