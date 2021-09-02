<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\DTO;

use Symfony\Component\Validator\Constraint;

interface ValidatableDtoInterface
{
    public static function getRequestConstraint(): Constraint;
}
