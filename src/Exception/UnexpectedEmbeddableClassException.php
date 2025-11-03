<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Exception;

class UnexpectedEmbeddableClassException extends \InvalidArgumentException
{
    public static function create(string $expectedClass, object $embeddableObject): self
    {
        return new self(
            \sprintf(
                'Expected embeddable object to be an instance of "%s", but got "%s".',
                $expectedClass,
                \get_class($embeddableObject)
            )
        );
    }
}
