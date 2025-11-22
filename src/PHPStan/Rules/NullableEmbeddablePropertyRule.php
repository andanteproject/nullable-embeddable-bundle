<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\PHPStan\Rules;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Embedded;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
class NullableEmbeddablePropertyRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        // Only check classes with both Embeddable and NullableEmbeddable attributes
        if (!$this->hasEmbeddableAttribute($classReflection->getName())) {
            return [];
        }

        if (!$this->hasNullableEmbeddableAttribute($classReflection->getName())) {
            return [];
        }

        $errors = [];
        $reflectionClass = new \ReflectionClass($classReflection->getName());

        foreach ($reflectionClass->getProperties() as $property) {
            // Skip static properties
            if ($property->isStatic()) {
                continue;
            }

            // Rule 1: Properties with non-null default values outside constructor
            if ($this->hasNonNullDefaultValue($property, $reflectionClass)) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf(
                        'Property %s::$%s in a NullableEmbeddable class has a non-null default value outside the constructor. '.
                        'Initialize it in the constructor instead to avoid issues with Doctrine hydration (Doctrine skips constructors during hydration).',
                        $classReflection->getName(),
                        $property->getName()
                    )
                )->identifier('nullableEmbeddable.propertyInitialization')->build();
            }

            // Rule 2: Properties with Column mapping must be nullable
            // Either explicitly via nullable=true OR implicitly via PHP nullable type
            $columnAttribute = $this->getColumnAttribute($property);
            if (null !== $columnAttribute) {
                $isExplicitlyNullable = $this->isColumnNullable($columnAttribute);
                $propertyType = $property->getType();
                $isPhpNullable = $propertyType instanceof \ReflectionNamedType && $propertyType->allowsNull();

                // Only flag if both explicit attribute AND PHP type are not nullable
                if (!$isExplicitlyNullable && !$isPhpNullable) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf(
                            'Property %s::$%s in a NullableEmbeddable class must be nullable (either via nullable=true in #[Column] or ?Type). '.
                            'When the embeddable object is null, Doctrine will set all its database columns to NULL.',
                            $classReflection->getName(),
                            $property->getName()
                        )
                    )->identifier('nullableEmbeddable.columnNullable')->build();
                }
            }

            // Rule 3: Embedded properties with explicit non-null defaults must be nullable
            // Note: Uninitialized embedded properties are fine - they remain uninitialized when parent is null
            $embeddedAttribute = $this->getEmbeddedAttribute($property);
            if (null !== $embeddedAttribute && $property->hasDefaultValue()) {
                $defaultValue = $property->getDefaultValue();
                $propertyType = $property->getType();

                // Only flag if there's an explicit non-null default value at class level
                if (null !== $defaultValue && $propertyType instanceof \ReflectionNamedType && !$propertyType->allowsNull()) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf(
                            'Property %s::$%s in a NullableEmbeddable class is an embedded object with a non-null default value and must be nullable. '.
                            'When the parent embeddable is null, all nested embeddables with default values must accept null.',
                            $classReflection->getName(),
                            $property->getName()
                        )
                    )->identifier('nullableEmbeddable.embeddedNullable')->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @param class-string $className
     */
    private function hasEmbeddableAttribute(string $className): bool
    {
        try {
            $reflectionClass = new \ReflectionClass($className);

            return !empty($reflectionClass->getAttributes(Embeddable::class));
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * @param class-string $className
     */
    private function hasNullableEmbeddableAttribute(string $className): bool
    {
        try {
            $reflectionClass = new \ReflectionClass($className);

            return !empty($reflectionClass->getAttributes(NullableEmbeddable::class));
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function hasNonNullDefaultValue(\ReflectionProperty $property, \ReflectionClass $reflectionClass): bool
    {
        // Check if property has a default value
        if (!$property->hasDefaultValue()) {
            return false;
        }

        $defaultValue = $property->getDefaultValue();

        // If default value is null, that's fine
        if (null === $defaultValue) {
            return false;
        }

        // Check if this property is initialized in the constructor
        if ($this->isInitializedInConstructor($property, $reflectionClass)) {
            return false;
        }

        // Non-null default value outside constructor
        return true;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function isInitializedInConstructor(\ReflectionProperty $property, \ReflectionClass $reflectionClass): bool
    {
        $constructor = $reflectionClass->getConstructor();

        if (null === $constructor) {
            return false;
        }

        // Check if property is a constructor parameter (promoted property)
        foreach ($constructor->getParameters() as $param) {
            if ($param->getName() === $property->getName() && $param->isPromoted()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \ReflectionAttribute<Column>|null
     */
    private function getColumnAttribute(\ReflectionProperty $property): ?\ReflectionAttribute
    {
        $attributes = $property->getAttributes(Column::class);

        return $attributes[0] ?? null;
    }

    /**
     * @param \ReflectionAttribute<Column> $columnAttribute
     */
    private function isColumnNullable(\ReflectionAttribute $columnAttribute): bool
    {
        $arguments = $columnAttribute->getArguments();

        // Check named argument
        if (isset($arguments['nullable'])) {
            return true === $arguments['nullable'];
        }

        // Column mapping defaults to nullable: false
        return false;
    }

    /**
     * @return \ReflectionAttribute<Embedded>|null
     */
    private function getEmbeddedAttribute(\ReflectionProperty $property): ?\ReflectionAttribute
    {
        $attributes = $property->getAttributes(Embedded::class);

        return $attributes[0] ?? null;
    }
}
