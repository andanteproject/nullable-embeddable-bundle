<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\Exception\LogicException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Metadata\Embedded;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
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
                    if (!\is_string($attribute->processor) || !\is_subclass_of($attribute->processor, ProcessorInterface::class)) {
                        throw new InvalidProcessorException(\sprintf('Processor "%s" for embeddable "%s" must implement "%s".', $attribute->processor, $embeddableClassName, ProcessorInterface::class));
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
