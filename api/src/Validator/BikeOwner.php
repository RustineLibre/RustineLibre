<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class BikeOwner extends Constraint
{
    public string $message = 'The bike should be your bike to modify it';
}