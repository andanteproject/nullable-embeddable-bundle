<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Unit\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorCallableException;
use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\ProcessorValidator;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity\Processor\InvalidProcessor;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity\Processor\AddressEmbeddableProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ProcessorValidatorTest extends TestCase
{
    private string $embeddableClassName = 'TestEmbeddable';
    private string $rootEntityClass = 'TestEntity';
    private string $propertyPath = 'testProperty';

    public function testAssertValidClassStringValid(): void
    {
        $processor = AddressEmbeddableProcessor::class;
        ProcessorValidator::assertValidClassString($processor, $this->embeddableClassName);
        $this->assertTrue(true); // No exception means success
    }

    public function testAssertValidClassStringInvalid(): void
    {
        $this->expectException(InvalidProcessorException::class);
        $this->expectExceptionMessage(\sprintf('Processor "%s" for embeddable "%s" must implement "%s".', InvalidProcessor::class, $this->embeddableClassName, ProcessorInterface::class));

        $processor = InvalidProcessor::class;
        ProcessorValidator::assertValidClassString($processor, $this->embeddableClassName);
    }

    public function testAssertValidClosureValid(): void
    {
        $processor = static function (
            PropertyAccessor $propertyAccessor,
            object $embeddableObject,
            PropertyPathInterface $propertyPath,
            object $rootEntity,
            mixed $embeddedConfig,
        ): Result {
            return Result::SHOULD_BE_NULL;
        };
        ProcessorValidator::assertValidClosure($processor, $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath);
        $this->assertTrue(true); // No exception means success
    }

    public function testAssertValidClosureInvalidReturnType(): void
    {
        $this->expectException(InvalidProcessorCallableException::class);
        $this->expectExceptionMessage(\sprintf('Callable processor for embeddable "%s" in entity "%s" at path "%s" must return "%s", "string" given.', $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath, Result::class));

        $processor = static function (): string {
            return 'invalid';
        };
        ProcessorValidator::assertValidClosure($processor, $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath);
    }

    public function testAssertValidClosureUnrecognizedArguments(): void
    {
        $this->expectException(InvalidProcessorCallableException::class);
        $this->expectExceptionMessage(\sprintf('Callable processor for embeddable "%s" in entity "%s" at path "%s" has unrecognized arguments: "unrecognizedArg".', $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath));

        $processor = static function (string $unrecognizedArg): Result {
            return Result::KEEP_INITIALIZED;
        };
        ProcessorValidator::assertValidClosure($processor, $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath);
    }

    public function testAssertValidClosureInvalidArgumentType(): void
    {
        $this->expectException(InvalidProcessorCallableException::class);
        $this->expectExceptionMessage(\sprintf('Callable processor argument "%s" for embeddable "%s" in entity "%s" at path "%s" expects type "%s", but type "%s" is not compatible.', 'embeddableObject', $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath, 'object', 'string'));

        $processor = static function (string $embeddableObject): Result {
            return Result::KEEP_INITIALIZED;
        };
        ProcessorValidator::assertValidClosure($processor, $this->embeddableClassName, $this->rootEntityClass, $this->propertyPath);
    }
}
