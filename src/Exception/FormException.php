<?php

namespace App\Exception;

use Exception;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class FormException.
 */
class FormException extends HttpException
{

    private FormInterface $form;

    /**
     * FormException constructor.
     *
     * @param FormInterface $form Form
     * @param string|null $message
     * @param Exception|null $previous
     * @param array $headers
     * @param int|null $code
     * @param int|null $statusCode statusCode
     */
    public function __construct(
        FormInterface $form,
        string $message = null,
        Exception $previous = null,
        array $headers = [],
        ?int $code = 0,
        ?int $statusCode = Response::HTTP_BAD_REQUEST
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->form = $form;
    }

    public function getErrors(): FormErrorIterator
    {
        return $this->form->getErrors(true);
    }
}
