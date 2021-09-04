# Symfony ParamConverter with request validation

The idea is to validate a raw payload and then map the request to object.
It just adds the validation step between decoding and denormalization.

## Installation

```shell
composer require revenkroz/validating-param-converter
```

Add service to your `services.yaml`:
```yaml
Revenkroz\ValidatingParamConverter\Request\ParamConverter\ValidatingParamConverter:
    class: Revenkroz\ValidatingParamConverter\Request\ParamConverter\ValidatingParamConverter
    tags:
        - { name: 'request.param_converter', priority: false }
```

## Usage

Create a DTO that implements `ValidatableParamInterface`:
```php
use Revenkroz\ValidatingParamConverter\Request\ValidatableParamInterface;

class YourDto implements ValidatableDtoInterface
{
    public static function getRequestConstraint(): Constraint
    {
        // ...
    }
}
```

Get your DTO in controller method:

```php
public function customAction(YourDto $dto, Request $request): Response {}
```

To validate a query using your validation groups, use the `CustomGroupsValidatableParamInterface` interface instead:
```php
use Revenkroz\ValidatingParamConverter\Request\CustomGroupsValidatableParamInterface;

class YourDto implements ValidatableDtoInterface
{
    public static function getRequestConstraint(): Constraint
    {
        // ...
    }

    public static function getRequestValidationGroups(): array
    {
        return ['one_group', 'another_group'];
    }
}
```
