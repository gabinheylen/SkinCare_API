<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ProduitDetailsConstraint extends Constraint
{
    public $message = 'The key "{{ key }}" or its value "{{ value }}" is invalid.';

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}
