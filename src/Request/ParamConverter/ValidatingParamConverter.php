<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\Request\ParamConverter;

use Revenkroz\ValidatingParamConverter\DTO\ValidatableDtoInterface;
use Revenkroz\ValidatingParamConverter\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatingParamConverter implements ParamConverterInterface
{
    private const DEFAULT_FORMAT = 'json';
    private const SUPPORTED_CONTENT_TYPES = [
        'application/json',
        'application/json-patch+json',
        'application/ld+json',
        'application/hal+json',
    ];

    public function __construct(
        private DecoderInterface $decoder,
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH], true)) {
            return;
        }

        if (!\in_array($request->headers->get('Content-Type'), self::SUPPORTED_CONTENT_TYPES, true)) {
            return;
        }

        try {
            $data = $this->decoder->decode($request->getContent(), self::DEFAULT_FORMAT);
        } catch (NotEncodableValueException $exception) {
            throw new BadRequestHttpException('Bad json.', $exception);
        }

        $constraint = \call_user_func([$configuration->getClass(), 'getRequestConstraint']);
        $violations = $this->validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $object = $this->denormalizer->denormalize($data, $configuration->getClass(), self::DEFAULT_FORMAT);

        $request->attributes->set($configuration->getName(), $object);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return is_subclass_of($configuration->getClass(), ValidatableDtoInterface::class);
    }
}
