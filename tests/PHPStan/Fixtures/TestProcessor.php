<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\PHPStan\Fixtures;

use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class TestProcessor implements ProcessorInterface
{
    public function analyze(
        PropertyAccessor $propertyAccessor,
        object $embeddableObject,
        PropertyPathInterface $propertyPath,
        object $rootEntity,
        mixed $embeddedConfig,
    ): Result {
        return Result::SHOULD_BE_NULL;
    }
}
