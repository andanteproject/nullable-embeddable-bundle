<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle;

use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

interface ProcessorInterface
{
    /**
     * @throws UnexpectedEmbeddableClassException
     */
    public function analyze(PropertyAccessor $propertyAccessor, object $embeddableObject, PropertyPathInterface $propertyPath, object $rootEntity, mixed $embeddedConfig): Result;
}
