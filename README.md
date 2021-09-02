# Symfony ParamConverter with request validation

The idea is to validate a raw payload and then map the request to object.
It just adds the validation step between decoding and denormalization.

## Installation

Add service to your `services.yaml`:
```yaml
Revenkroz\ValidatingParamConverter\Request\ParamConverter\ValidatingParamConverter:
    class: Revenkroz\ValidatingParamConverter\Request\ParamConverter\ValidatingParamConverter
    tags:
        - { name: 'request.param_converter', priority: false }
```

## Usage

Create a DTO that implements `ValidatableDtoInterface`:
```php
class YourDto implements ValidatableDtoInterface
{
    // ...
}
```

Get your DTO in controller method:

```php
public function customAction(YourDto $dto, Request $request): Response {}
```
