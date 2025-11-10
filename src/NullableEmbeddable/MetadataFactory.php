<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\Exception\LogicException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Metadata\Embedded;
use Andante\NullableEmbeddableBundle\Util\EmbeddablePropertySorter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyPath;

class MetadataFactory
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @param class-string $entityClassFqcn
     *
     * @throws InvalidProcessorException
     */
    public function create(string $entityClassFqcn): Metadata
    {
        $manager = $this->managerRegistry->getManagerForClass($entityClassFqcn);
        if (null === $manager) {
            throw new LogicException(\sprintf('Cannot find manager for class "%s".', $entityClassFqcn));
        }

        /** @var ClassMetadata<object> $classMetadata */
        $classMetadata = $manager->getClassMetadata($entityClassFqcn);
        $embeddableClasses = $classMetadata->embeddedClasses;

        $embeds = [];

        $embeddableProperties = EmbeddablePropertySorter::sortDeepestFirst($embeddableClasses);
        foreach ($embeddableProperties as $embeddablePropertyPath => $embeddableConfig) {
            if (\is_array($embeddableConfig)) {
                $embeddableClassName = $embeddableConfig['class'] ?? throw new LogicException(\sprintf('key "class" is expected on a doctrine Embeddable Config'));
            } elseif (\is_object($embeddableConfig) && \is_a($embeddableConfig, 'Doctrine\ORM\Mapping\EmbeddedClassMapping')) {
                /** @var object{ class: class-string } $embeddableConfig */
                $embeddableClassName = $embeddableConfig->class;
            } else {
                throw new LogicException(\sprintf('unrecognized embeddable config "%s"', \get_debug_type($embeddableConfig)));
            }

            $reflectionAttributes = (new \ReflectionClass($embeddableClassName))->getAttributes(NullableEmbeddable::class);

            $attributes = [];
            foreach ($reflectionAttributes as $reflectionAttribute) {
                /** @var NullableEmbeddable $attribute */
                $attribute = $reflectionAttribute->newInstance();
                if (null !== $attribute->processor) {
                    if (\is_string($attribute->processor)) {
                        ProcessorValidator::assertValidClassString($attribute->processor, $embeddableClassName);
                    } elseif ($attribute->processor instanceof \Closure) {
                        ProcessorValidator::assertValidClosure($attribute->processor, $embeddableClassName, $entityClassFqcn, $embeddablePropertyPath);
                    } else {
                        // This else block should ideally be unreachable due to PHP's type system,
                        // but kept for defensive programming if the type system is bypassed.
                        throw new LogicException(\sprintf('Processor must be a string (class-string) or a \\Closure, "%s" given.', \get_debug_type($attribute->processor)));
                    }
                }
                $attributes[] = $attribute;
            }

            $embeds[] = new Embedded(
                propertyPath: new PropertyPath($embeddablePropertyPath),
                class: $embeddableClassName,
                nullableEmbeddableAttributes: $attributes,
                doctrineEmbeddableConfig: $embeddableConfig
            );
        }

        return new Metadata(
            entityClassFqcn: $entityClassFqcn,
            embeds: $embeds,
        );
    }
}
