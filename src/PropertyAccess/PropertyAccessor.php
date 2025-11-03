<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\PropertyAccess;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess as SfPropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor as SfPropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PropertyAccessor implements PropertyAccessorInterface
{
    private SfPropertyAccessor $propertyAccessor;

    /** @var array<string, bool> */
    private $uninitializedPropertiesRegistry = [];

    public function __construct(SfPropertyAccessor $propertyAccess)
    {
        $this->propertyAccessor = $propertyAccess;
    }

    public static function create(): self
    {
        return new self(SfPropertyAccess::createPropertyAccessor());
    }

    public function forgetAll(): static
    {
        $this->uninitializedPropertiesRegistry = [];

        return $this;
    }

    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\OutOfBoundsException
     * @throws AccessException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     */
    /**
     * @param array<string, mixed>|object $objectOrArray
     */
    public function setValue(object|array &$objectOrArray, string|PropertyPathInterface $propertyPath, mixed $value): void
    {
        $this->propertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        if (\is_object($objectOrArray)) {
            // Let's clear the uninitializedPropertiesRegistry, because we achieved to set a value now
            $this->forgetAsUninitialized($objectOrArray, $propertyPath);
        }
    }

    /**
     * @param array<string, mixed>|object $objectOrArray
     */
    public function getValue(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): mixed
    {
        return $this->propertyAccessor->getValue($objectOrArray, $propertyPath);
    }

    /**
     * @param array<string, mixed>|object $objectOrArray
     */
    public function isWritable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return $this->propertyAccessor->isWritable($objectOrArray, $propertyPath);
    }

    /**
     * @param array<string, mixed>|object $objectOrArray
     */
    public function isReadable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return $this->propertyAccessor->isReadable($objectOrArray, $propertyPath);
    }

    /**
     * @param array<array-key, mixed>|object $objectOrArray
     *
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws AccessException
     */
    public function isUninitialized(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        if (\is_object($objectOrArray) && $this->hasObjAndPropertyPathRegisteredAsUninitialized($objectOrArray, $propertyPath)) {
            return true;
        }
        try {
            $this->propertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (AccessException $e) {
            if (!$e instanceof UninitializedPropertyException
                && (
                    \class_exists(UninitializedPropertyException::class)
                    || \str_contains('You should initialize it', $e->getMessage())
                )
            ) {
                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\OutOfBoundsException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws AccessException
     */
    public function rememberAsUninitialized(object $object, string|PropertyPathInterface $embeddablePropertyPath): void
    {
        if (!$embeddablePropertyPath instanceof PropertyPathInterface) {
            $embeddablePropertyPath = new PropertyPath($embeddablePropertyPath);
        }
        $subPaths = $this->getSubPathTree($embeddablePropertyPath);
        /** @var array<array-key, mixed>|object $subObject */
        $subObject = $object;
        foreach ($subPaths as $path) {
            if (\is_object($subObject)) {
                $this->rememberObjAndPropertyPathAsUninitialized($subObject, $path);
            }
            /** @var array<array-key, mixed>|object $subObject */
            $subObject = $this->getValue($subObject, $path->getElement(0));
        }
    }

    /**
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\PropertyAccess\Exception\OutOfBoundsException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     * @throws AccessException
     */
    public function forgetAsUninitialized(object $object, string|PropertyPathInterface $embeddablePropertyPath): void
    {
        if (!$embeddablePropertyPath instanceof PropertyPathInterface) {
            $embeddablePropertyPath = new PropertyPath($embeddablePropertyPath);
        }
        $subPaths = $this->getSubPathTree($embeddablePropertyPath);
        /** @var array<array-key, mixed>|object $subObject */
        $subObject = $object;
        foreach ($subPaths as $path) {
            if (\is_object($subObject)) {
                $this->forgetObjAndPropertyPathAsUninitialized($subObject, $path);
            }
            /** @var array<array-key, mixed>|object $subObject */
            $subObject = $this->getValue($subObject, $path->getElement(0));
        }
    }

    private function rememberObjAndPropertyPathAsUninitialized(object $object, string|PropertyPathInterface $propertyPath): void
    {
        $this->uninitializedPropertiesRegistry[\sprintf('%s|%s', \spl_object_hash($object), $propertyPath)] = true;
    }

    private function forgetObjAndPropertyPathAsUninitialized(object $object, string|PropertyPathInterface $propertyPath): void
    {
        unset($this->uninitializedPropertiesRegistry[\sprintf('%s|%s', \spl_object_hash($object), $propertyPath)]);
    }

    private function hasObjAndPropertyPathRegisteredAsUninitialized(object $object, string|PropertyPathInterface $propertyPath): bool
    {
        return isset($this->uninitializedPropertiesRegistry[\sprintf('%s|%s', \spl_object_hash($object), $propertyPath)]);
    }

    /**
     * @return list<PropertyPathInterface>
     *
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     */
    /**
     * @param list<PropertyPathInterface> $carry
     *
     * @return list<PropertyPathInterface>
     *
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException
     */
    private function getSubPathTree(PropertyPathInterface $propertyPath, array $carry = []): array
    {
        $carry[] = $propertyPath;
        $elements = $propertyPath->getElements();
        \array_shift($elements);
        if (\count($elements) > 0) {
            $carry = $this->getSubPathTree(new PropertyPath(\implode('.', $elements)), $carry);
        }

        return $carry;
    }
}
