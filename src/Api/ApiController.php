<?php

namespace App\Api;

use App\Exception\FormException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiController extends AbstractController
{

    /**
     * @param Request $request
     * @param string $formClass
     * @return array
     */
    protected function validateRequestWithClass(Request $request, string $formClass): array
    {
        $form = $this->createForm($formClass);
        $form->submit($request->request->all());
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new FormException($form);
        }
        return $form->getData();
    }
}