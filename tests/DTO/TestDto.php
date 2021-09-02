<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\Tests\DTO;

use Revenkroz\ValidatingParamConverter\DTO\ValidatableDtoInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class TestDto implements ValidatableDtoInterface
{
    public string $firstVar = '';
    public int $secondVar = 0;

    public static function getRequestConstraint(): Constraint
    {
        return new Assert\Collection([
            'firstVar' => new Assert\Required([
                new Assert\Choice(['this', 'that']),
            ]),
            'secondVar' => new Assert\Required([
                new Assert\Range([
                    'min' => 0,
                    'max' => 3,
                ]),
            ]),
        ]);
    }
}
