<?php

namespace App\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FormException.
 */
class FormException extends ApiException
{

    private FormInterface $form;

    /**
     * FormException constructor.
     *
     * @param FormInterface $form Form
     */
    public function __construct(
        FormInterface $form
    ) {
        parent::__construct(null, 'form.bad_request', [], 0, Response::HTTP_BAD_REQUEST);
        $this->form = $form;
    }

    public function getErrors(): FormErrorIterator
    {
        return $this->form->getErrors(true);
    }
}
