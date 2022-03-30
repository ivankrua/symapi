<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ConflictException extends ApiException
{

    public function __construct(string $msg, array $params = [], string $langFile = 'validators')
    {
        parent::__construct(null, $msg, $params, 0, Response::HTTP_CONFLICT, $langFile);
    }
}