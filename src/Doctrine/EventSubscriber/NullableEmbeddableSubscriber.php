<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Doctrine\EventSubscriber;

use Andante\NullableEmbeddableBundle\Exception\LogicException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Registry;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class NullableEmbeddableSubscriber
{
    private PropertyAccessor $propertyAccessor;
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->propertyAccessor = PropertyAccessor::create();
        $this->registry = $registry;
    }

    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\AccessException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     * @throws \Symfony\Component\PropertyAccess\Exception\OutOfBoundsException
     * @throws LogicException
     */
    public function postLoad(PostLoadEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        /** @var class-string $entityClass */
        $entityClass = \get_class($entity);

        $nullableEmbeddableMetadata = $this->registry->getNullableEmbeddableMetadata($entityClass);

        $embeds = $nullableEmbeddableMetadata->getEmbeds();
        if (\count($embeds) > 0) {
            foreach ($embeds as $embedded) {
                foreach ($embedded->getNullableEmbeddableAttributes() as $attribute) {
                    $processor = $attribute->processor;
                    $embeddablePropertyPath = $embedded->getPropertyPath();

                    /** @var object $entity */
                    /** @var object $embeddable */
                    $embeddable = $this->propertyAccessor->getValue($entity, $embeddablePropertyPath);

                    if (\is_string($processor)) {
                        /** @var ProcessorInterface $processorInstance */
                        $processorInstance = new $processor();
                        $result = $processorInstance->analyze(
                            propertyAccessor: $this->propertyAccessor,
                            embeddableObject: $embeddable,
                            propertyPath: $embeddablePropertyPath,
                            rootEntity: $entity,
                            embeddedConfig: $embedded->getDoctrineConfig()
                        );
                    } elseif ($processor instanceof \Closure) {
                        $reflectionFunction = new \ReflectionFunction($processor);
                        $parameters = $reflectionFunction->getParameters();
                        $args = [];

                        $availableArgs = [
                            'propertyAccessor' => $this->propertyAccessor,
                            'embeddableObject' => $embeddable,
                            'propertyPath' => $embeddablePropertyPath,
                            'rootEntity' => $entity,
                            'embeddedConfig' => $embedded->getDoctrineConfig(),
                        ];

                        foreach ($parameters as $parameter) {
                            $paramName = $parameter->getName();
                            if (isset($availableArgs[$paramName])) {
                                $args[$paramName] = $availableArgs[$paramName];
                            } elseif ($parameter->isDefaultValueAvailable()) {
                                $args[$paramName] = $parameter->getDefaultValue();
                            } else {
                                // This case should ideally not be reached if MetadataFactory validates arguments
                                // For now, we assume all required parameters will be available in $availableArgs
                            }
                        }
                        $result = $processor(...$args);
                    } else {
                        throw new LogicException(\sprintf('Processor must be a string (class-string) or a \\Closure, "%s" given.', \get_debug_type($processor)));
                    }

                    switch ($result) {
                        case Result::SHOULD_BE_NULL:
                            try {
                                $this->propertyAccessor->setValue($entity, $embeddablePropertyPath, null);
                            } catch (\InvalidArgumentException|NoSuchPropertyException $throwable) {
                                // Ok, null it's not allowed.
                                // Which means we need to register this property as uninitialized in out property accessor
                                // so the other NullableEmbeddablePropertyProcessors can behave as expected
                                $this->propertyAccessor->rememberAsUninitialized((object) $entity, $embeddablePropertyPath); // Explicitly cast to object
                                // I'M SURE you configured the entities the right way and some parent is null.
                                // RIGHT? ʘ‿ʘ
                            }
                            break;
                        case Result::KEEP_INITIALIZED:
                            // Ok, let's do nothing! ¯\_(ツ)_/¯
                            break;
                        default:
                            throw new LogicException(\sprintf('%s do not handle result "%s". Please handle this result as well. ', __METHOD__, $result->value));
                    }
                }
            }
            /*
             * PLEASE NOTE: Property accessors remembers uninitialized properties through the spl_object_hash() function
             * so we need to clear the property accessor memory in order to avoid conflicts when a spl_object_hash()
             * has been reused.
             *
             * @see https://www.php.net/manual/en/function.spl_object_hash.php
             *
             * "This id can be used as a hash key for storing objects, or for identifying an object,
             * as long as the object is not destroyed. Once the object is destroyed, its hash may be reused for other objects."
             */
            $this->propertyAccessor->forgetAll();
        }
    }
}
