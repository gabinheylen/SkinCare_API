<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProfileDataConstraintValidator extends ConstraintValidator
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
/*J'ai deux entités, Produit et ProfilDermatologique, ces deux entités ont respectivement details et profiledata qui sont deux JSON comme ceci :
{
            "skinTone": "Light",
            "typeOfSkin": "Oily",
            "skinUndertone": "Cool",
            "medicalHistory": "None",
            "skincareHabits": "Regular",
            "skinSensitivity": "Medium",
            "lifestyleFactors": "High stress",
            "commonSkinProblems": "Acne",
            "allergiesIntolerances": "None",
            "environmentalConditions": "Urban"
        }

Il faudrait que tu me crée un controller avec une route evaluate product qui prendrait l'id du produit en paramettre et grace au token de la personne qui a fait la requete, trouverait le profil dermatologique, recupererait les deux JSON et les comparerait les differentes parties du json pour determiner une note du produit en fonction du profil dermatologique. Tout cela pour savoir si un produit est compatible avec la peau d'une personne. La route renverrais un json avec tous les parametres defini dans celui que je t'ai passé, en mettant une note pour savoir si chaque partie est compatible (ca n'est pas parce que  "skincareHabits": "Regular", chez la personne mais que sur le produit c'est Irregular que le produit ne feat pas completement, il y a une logique que tu devras tenir (voici les differents choses que tu peux avoir :
ALLOWED_VALUES = [
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


*/