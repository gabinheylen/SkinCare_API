<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProduitDetailsConstraintValidator extends ConstraintValidator
{
    private const ALLOWED_KEYS = [
        'typeOfSkin', 'skinSensitivity', 'commonSkinProblems', 'skinTone',
        'skinUndertone', 'environmentalConditions', 'skincareHabits',
        'allergiesIntolerances', 'lifestyleFactors', 'medicalHistory'
    ];

    private const ALLOWED_VALUES = [
        'typeOfSkin' => ['Oily', 'Dry', 'Combination', 'Normal'],
        'skinSensitivity' => ['High', 'Medium', 'Low'],
        'commonSkinProblems' => ['Acne', 'Eczema', 'Psoriasis', 'None'],
        'skinTone' => ['Light', 'Medium', 'Dark'],
        'skinUndertone' => ['Cool', 'Warm', 'Neutral'],
        'environmentalConditions' => ['Urban', 'Rural', 'Suburban'],
        'skincareHabits' => ['Regular', 'Irregular', 'None'],
        'allergiesIntolerances' => ['None', 'Fragrance', 'Preservatives', 'Dyes'],
        'lifestyleFactors' => ['High stress', 'Low stress', 'Moderate stress'],
        'medicalHistory' => ['Eczema', 'Psoriasis', 'None']
    ];

    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $key => $val) {
            if (!in_array($key, self::ALLOWED_KEYS)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ key }}', $key)
                    ->setParameter('{{ value }}', json_encode($val))
                    ->addViolation();
                continue;
            }

            if (!in_array($val, self::ALLOWED_VALUES[$key])) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ key }}', $key)
                    ->setParameter('{{ value }}', json_encode($val))
                    ->addViolation();
            }
        }
    }
}
