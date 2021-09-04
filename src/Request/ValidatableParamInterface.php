<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\Request;

use Symfony\Component\Validator\Constraint;

interface ValidatableParamInterface
{
    public static function getRequestConstraint(): Constraint;
}
