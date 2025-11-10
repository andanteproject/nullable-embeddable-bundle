<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity\Processor;

use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity\Address;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class AddressEmbeddableProcessor implements ProcessorInterface
{
    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\AccessException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws UnexpectedEmbeddableClassException
     */
    public function analyze(PropertyAccessor $propertyAccessor, object $embeddableObject, PropertyPathInterface $propertyPath, object $rootEntity, mixed $embeddedConfig): Result
    {
        if (!$embeddableObject instanceof Address) {
            throw UnexpectedEmbeddableClassException::create(Address::class, $embeddableObject);
        }

        if (
            null === $propertyAccessor->getValue($embeddableObject, 'street')
            && null === $propertyAccessor->getValue($embeddableObject, 'city')
            && null === $propertyAccessor->getValue($embeddableObject, 'country')
        ) {
            return Result::SHOULD_BE_NULL;
        }

        return Result::KEEP_INITIALIZED;
    }
}
