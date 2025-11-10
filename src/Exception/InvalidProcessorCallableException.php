<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Exception;

class InvalidProcessorCallableException extends \LogicException
{
    public static function createForInvalidReturnType(string $expectedReturnType, string $actualReturnType, string $embeddableClassName, string $rootEntityClass, string $propertyPath): self
    {
        return new self(\sprintf('Callable processor for embeddable "%s" in entity "%s" at path "%s" must return "%s", "%s" given.', $embeddableClassName, $rootEntityClass, $propertyPath, $expectedReturnType, $actualReturnType));
    }

    /**
     * @param list<string> $unrecognizedArguments
     */
    public static function createForUnrecognizedArguments(array $unrecognizedArguments, string $embeddableClassName, string $rootEntityClass, string $propertyPath): self
    {
        return new self(\sprintf('Callable processor for embeddable "%s" in entity "%s" at path "%s" has unrecognized arguments: "%s".', $embeddableClassName, $rootEntityClass, $propertyPath, \implode('", "', $unrecognizedArguments)));
    }

    public static function createForInvalidArgumentType(string $argumentName, string $expectedType, string $actualType, string $embeddableClassName, string $rootEntityClass, string $propertyPath): self
    {
        return new self(\sprintf('Callable processor argument "%s" for embeddable "%s" in entity "%s" at path "%s" expects type "%s", but type "%s" is not compatible.', $argumentName, $embeddableClassName, $rootEntityClass, $propertyPath, $expectedType, $actualType));
    }
}
