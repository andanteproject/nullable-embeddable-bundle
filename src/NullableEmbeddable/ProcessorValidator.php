<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorCallableException;
use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\Exception\LogicException;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ProcessorValidator
{
    /**
     * @throws InvalidProcessorException
     */
    public static function assertValidClassString(string $processor, string $embeddableClassName): void
    {
        if (!\is_a($processor, ProcessorInterface::class, true)) {
            throw new InvalidProcessorException(\sprintf('Processor "%s" for embeddable "%s" must implement "%s".', $processor, $embeddableClassName, ProcessorInterface::class));
        }
    }

    /**
     * @throws InvalidProcessorCallableException
     * @throws LogicException
     */
    public static function assertValidClosure(\Closure $processor, string $embeddableClassName, string $rootEntityClass, string $propertyPath): void
    {
        $reflectionFunction = new \ReflectionFunction($processor);

        // Check return type
        $returnType = $reflectionFunction->getReturnType();
        if (
            !($returnType instanceof \ReflectionNamedType)
            || Result::class !== $returnType->getName()
        ) {
            throw InvalidProcessorCallableException::createForInvalidReturnType(Result::class, $returnType instanceof \ReflectionNamedType ? $returnType->getName() : 'unknown', $embeddableClassName, $rootEntityClass, $propertyPath);
        }

        // Check arguments
        $expectedArgs = [
            'propertyAccessor' => PropertyAccessor::class,
            'embeddableObject' => 'object',
            'propertyPath' => PropertyPathInterface::class,
            'rootEntity' => 'object',
            'embeddedConfig' => 'mixed',
        ];
        $unrecognizedArgs = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            if (!isset($expectedArgs[$paramName])) {
                $unrecognizedArgs[] = $paramName;
            } else {
                $expectedType = $expectedArgs[$paramName];
                $parameterType = $parameter->getType();

                if (null !== $parameterType) {
                    $actualType = $parameterType instanceof \ReflectionNamedType ? $parameterType->getName() : (string) $parameterType;

                    // Simple compatibility check for now. More complex checks might be needed for interfaces/inheritance.
                    // 'object' is a special case for embeddableObject and rootEntity
                    if ('object' === $expectedType) {
                        if ('object' !== $actualType && !\class_exists($actualType) && !\interface_exists($actualType)) {
                            throw InvalidProcessorCallableException::createForInvalidArgumentType($paramName, $expectedType, $actualType, $embeddableClassName, $rootEntityClass, $propertyPath);
                        }
                    } elseif ('mixed' !== $expectedType && $expectedType !== $actualType) {
                        throw InvalidProcessorCallableException::createForInvalidArgumentType($paramName, $expectedType, $actualType, $embeddableClassName, $rootEntityClass, $propertyPath);
                    }
                }
            }
        }
        if (\count($unrecognizedArgs) > 0) {
            throw InvalidProcessorCallableException::createForUnrecognizedArguments($unrecognizedArgs, $embeddableClassName, $rootEntityClass, $propertyPath);
        }
    }
}
