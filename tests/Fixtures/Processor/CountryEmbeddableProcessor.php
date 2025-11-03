<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\Processor;

use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity\Country;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class CountryEmbeddableProcessor implements ProcessorInterface
{
    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\AccessException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws UnexpectedEmbeddableClassException
     */
    public function analyze(PropertyAccessor $propertyAccessor, object $embeddableObject, PropertyPathInterface $propertyPath, object $rootEntity, mixed $embeddedConfig): Result
    {
        if (!$embeddableObject instanceof Country) {
            throw UnexpectedEmbeddableClassException::create(Country::class, $embeddableObject);
        }

        if ($propertyAccessor->isUninitialized($embeddableObject, 'code')) {
            return Result::SHOULD_BE_NULL;
        }

        return Result::KEEP_INITIALIZED;
    }
}
