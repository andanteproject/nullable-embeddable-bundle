<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures;

use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class NotNullColumnProcessor implements ProcessorInterface
{
    public function analyze(
        PropertyAccessor $propertyAccessor,
        object $embeddableObject,
        PropertyPathInterface $propertyPath,
        object $rootEntity,
        mixed $embeddedConfig,
    ): Result {
        if (!$embeddableObject instanceof NotNullColumnEmbeddable) {
            throw UnexpectedEmbeddableClassException::create(NotNullColumnEmbeddable::class, $embeddableObject);
        }

        // If street is null, the whole embeddable should be null
        if (null === $propertyAccessor->getValue($embeddableObject, 'street')) {
            return Result::SHOULD_BE_NULL;
        }

        return Result::KEEP_INITIALIZED;
    }
}
