<?php

namespace App\Serializer;

use App\Exception\FormException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class FormExceptionNormalizer.
 */
class FormExceptionNormalizer implements NormalizerInterface
{

    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [];
        $errors = $object->getErrors();
        foreach ($errors as $error) {
            $data[$error->getOrigin()->getName()][] = $error->getMessage();
        }
        return $data;
    }

    /**
     * @param mixed $data
     * @param null $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof FormException;
    }
}
