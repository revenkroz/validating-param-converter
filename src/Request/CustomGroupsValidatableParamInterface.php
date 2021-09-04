<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\Request;

interface CustomGroupsValidatableParamInterface extends ValidatableParamInterface
{
    public static function getRequestValidationGroups(): array;
}
