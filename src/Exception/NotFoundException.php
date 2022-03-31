<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends ApiException
{

    public function __construct(string $msg, array $params = [], string $langFile = 'validators')
    {
        parent::__construct(null, $msg, $params, 0, Response::HTTP_NOT_FOUND, $langFile);
    }
}