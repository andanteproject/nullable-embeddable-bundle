<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable\Metadata;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class Embedded
{
    public function __construct(
        private PropertyPathInterface $propertyPath,
        /** @var class-string */
        private string $class,
        /** @var list<NullableEmbeddable> */
        private array $nullableEmbeddableAttributes,
        private mixed $doctrineEmbeddableConfig,
    ) {
    }

    public function getPropertyPath(): PropertyPathInterface
    {
        return $this->propertyPath;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return list<NullableEmbeddable>
     */
    public function getNullableEmbeddableAttributes(): array
    {
        return $this->nullableEmbeddableAttributes;
    }

    public function getDoctrineConfig(): mixed
    {
        return $this->doctrineEmbeddableConfig;
    }
}
