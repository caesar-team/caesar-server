<?php

namespace App\Form;

use Symfony\Component\Form\Form;

class FormErrorSerializer
{
    /**
     * @param Form $form
     *
     * @return array
     */
    public function getFormErrorsAsArray(Form $form)
    {
        $errorIterators = [];

        foreach ($form as $fieldName => $formField) {
            $errorIterators[$fieldName] = $formField->getErrors();
        }

        $errors = [];
        foreach ($errorIterators as $key => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $errors[$key][] = $error->getMessage();
            }
        }

        return $errors;
    }
}
