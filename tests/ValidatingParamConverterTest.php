<?php declare(strict_types=1);

namespace Revenkroz\ValidatingParamConverter\Tests;

use PHPUnit\Framework\TestCase;
use Revenkroz\ValidatingParamConverter\Exception\ValidationException;
use Revenkroz\ValidatingParamConverter\Request\ParamConverter\ValidatingParamConverter;
use Revenkroz\ValidatingParamConverter\Tests\DTO\TestDto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

final class ValidatingParamConverterTest extends TestCase
{
    private const PARAM_KEY = 'param';
    private const CONVERTER_NAME = 'test';

    private ?ValidatingParamConverter $converter = null;

    protected function setUp(): void
    {
        $encoder = new JsonEncoder();
        $this->converter = new ValidatingParamConverter(
            $encoder,
            new Serializer([new ObjectNormalizer()], [$encoder]),
            Validation::createValidator()
        );
    }

    public function testItTransformsJsonToDtoOk(): void
    {
        $firstVar = 'this';
        $secondVar = 2;

        $request = $this->createRequest([
            'firstVar' => $firstVar,
            'secondVar' => $secondVar,
        ], 'POST');

        $manager = new ParamConverterManager();
        $manager->add($this->converter, 0, self::CONVERTER_NAME);
        $manager->apply($request, [$this->createConfiguration()]);

        $dto = $request->attributes->get(self::PARAM_KEY);
        $this->assertInstanceOf(TestDto::class, $dto);
        $this->assertSame($firstVar, $dto->firstVar);
        $this->assertSame($secondVar, $dto->secondVar);
    }

    public function testValidationFail(): void
    {
        $request = $this->createRequest([
            'fail' => 'shit',
        ], 'POST');

        $manager = new ParamConverterManager();
        $manager->add($this->converter, 0, self::CONVERTER_NAME);

        $this->expectException(ValidationException::class);
        $manager->apply($request, [$this->createConfiguration()]);
    }

    public function testUnsupportedMethod(): void
    {
        $request = $this->createRequest([
            'anithing' => 'yeah',
        ], 'GET');

        $manager = new ParamConverterManager();
        $manager->add($this->converter, 0, self::CONVERTER_NAME);
        $manager->apply($request, [$this->createConfiguration()]);

        $originalData = $request->attributes->get(self::PARAM_KEY);
        $this->assertNull($originalData);
    }

    public function testSendingBadContent(): void
    {
        $request = $this->createRequest('bad-content', 'POST');

        $manager = new ParamConverterManager();
        $manager->add($this->converter, 0, self::CONVERTER_NAME);

        $this->expectException(BadRequestHttpException::class);
        $manager->apply($request, [$this->createConfiguration()]);
    }

    public function testSendingUnsupportedFormat(): void
    {
        $request = $this->createRequest('unsupported', 'POST');
        $request->headers->set('Content-Type', 'text/html; charset=utf-8');

        $manager = new ParamConverterManager();
        $manager->add($this->converter, 0, self::CONVERTER_NAME);

        $this->expectException(BadRequestHttpException::class);
        $manager->apply($request, [$this->createConfiguration()]);
    }

    private function createConfiguration(): ParamConverter
    {
        return new ParamConverter([
            'name' => self::PARAM_KEY,
            'class' => TestDto::class,
            'converter' => self::CONVERTER_NAME,
        ]);
    }

    private function createRequest(array|string $body, string $method): Request
    {
        if (\is_array($body)) {
            $body = json_encode($body, \JSON_THROW_ON_ERROR);
        }

        $request = new Request([], [], [], [], [], [], $body);
        $request->headers->set('Content-Type', 'application/json');
        $request->setMethod($method);

        return $request;
    }
}
