<?php

namespace App\Exception;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiException extends HttpException
{
    private $data;
    private array $params;
    private string $langFile;

    /**
     * FormException constructor.
     *
     */
    public function __construct(
        $data = null,
        string $message = null,
        array $params = [],
        ?int $code = 0,
        ?int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        string $langFile = 'validators'
    ) {
        parent::__construct($statusCode, $message, null, [], $code);
        $this->data = $data;
        $this->params = $params;
        $this->langFile = $langFile;
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getLangFile(): string
    {
        return $this->langFile;
    }


}