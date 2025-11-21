![Andante Project Logo](https://github.com/andanteproject/nullable-embeddable-bundle/blob/main/andanteproject-logo.png?raw=true)
# Nullable Embeddable Bundle
#### Symfony Bundle - [AndanteProject](https://github.com/andanteproject)
[![Latest Version](https://img.shields.io/github/release/andanteproject/nullable-embeddable-bundle.svg)](https://github.com/andanteproject/nullable-embeddable-bundle/releases)
![Github actions](https://github.com/andanteproject/nullable-embeddable-bundle/actions/workflows/ci.yml/badge.svg?branch=main)
[![codecov](https://codecov.io/gh/andanteproject/nullable-embeddable-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/andanteproject/nullable-embeddable-bundle)
![Framework](https://img.shields.io/badge/Symfony-5.x|6.x|7.x-informational?Style=flat&logo=symfony)
![Php8](https://img.shields.io/badge/PHP-8.1|8.5%2B-informational?style=flat&logo=php)
![PhpStan](https://img.shields.io/badge/PHPStan-Level%209-success?style=flat&logo=php)

A Symfony Bundle that extends [Doctrine Embeddables](https://www.doctrine-project.org/projects/doctrine-orm/en/3.5/tutorials/embeddables.html) to allow them to be nullable with **custom business logic** to precisely determine their null state, **handling null** and **uninitialized properties**, addressing a common limitation in Doctrine ORM.

## Introduction

[Doctrine Embeddables](https://www.doctrine-project.org/projects/doctrine-orm/en/3.5/tutorials/embeddables.html) are powerful for encapsulating value objects, but they inherently cannot be null. This bundle provides a flexible solution to this limitation by introducing the `#[NullableEmbeddable]` attribute. This attribute allows you to define custom logic, either through a dedicated [processor class](#processor-interface) or a [static anonymous function (PHP 8.5+)](#anonymous-function-processor-php-85), to determine when an embeddable object should be considered null. This enables precise control over the null state, even handling uninitialized properties safely.

The bundle works seamlessly with multiple levels of embedded objects, processing from the deepest leaf embeddable up to the root entity.

For example, a `Country` embeddable can be marked as nullable based on an uninitialized property:

```php
<?php
// ... use statements
use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: static function (PropertyAccessor $propertyAccessor, object $embeddableObject): Result {
    // We check if the 'code' property is uninitialized.
    if ($propertyAccessor->isUninitialized($embeddableObject, 'code')) {
        return Result::SHOULD_BE_NULL;
    }
    return Result::KEEP_INITIALIZED;
})]
class Country
{
    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 2, nullable: true)]
        private string $code,
    ) {
    }
    // ... getters and setters
}
```

## Requirements
*   Symfony 5.x-7.x
*   PHP 8.1+ (PHP 8.5+ for anonymous function processors)
*   [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/3.5/tutorials/embeddables.html)

## Install
Via [Composer](https://getcomposer.org/):
```bash
$ composer require andanteproject/nullable-embeddable-bundle
```

After installation, make sure you have the bundle registered in your Symfony bundles list (`config/bundles.php`):
```php
return [
    // ...
    Andante\NullableEmbeddableBundle\AndanteNullableEmbeddableBundle::class => ['all' => true],
    // ...
];
```
This should be done automatically if you are using [Symfony Flex](https://flex.symfony.com). Otherwise, register it manually.

## Usage

The core of this bundle is the `#[NullableEmbeddable]` attribute, which you place on your Doctrine Embeddable classes alongside `#[ORM\Embeddable]`. This attribute requires a `processor` argument, which can be either a class implementing [ProcessorInterface](#processor-interface) or a [static anonymous function (PHP 8.5+)](#anonymous-function-processor-php-85).

### Processor Interface

For older PHP versions or more complex logic that warrants a dedicated class, you can implement the `ProcessorInterface`.

```php
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
```

Your processor class must implement this interface.

**Example: Address Embeddable with Class Processor**

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\ORM\Mapping as ORM;
use App\Processor\AddressEmbeddableProcessor; // Your custom processor

#[ORM\Embeddable]
#[NullableEmbeddable(processor: AddressEmbeddableProcessor::class)]
class Address
{
    // ... properties, getters, setters
}
```

And the corresponding `AddressEmbeddableProcessor` class:

```php
<?php

declare(strict_types=1);

namespace App\Processor;

use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use App\Entity\Address;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class AddressEmbeddableProcessor implements ProcessorInterface
{
    public function analyze(PropertyAccessor $propertyAccessor, object $embeddableObject, PropertyPathInterface $propertyPath, object $rootEntity, mixed $embeddedConfig): Result
    {
        if (!$embeddableObject instanceof Address) {
            throw UnexpectedEmbeddableClassException::create(Address::class, $embeddableObject);
        }

        if (
            null === $propertyAccessor->getValue($embeddableObject, 'street')
            && null === $propertyAccessor->getValue($embeddableObject, 'city')
            && null === $propertyAccessor->getValue($embeddableObject, 'country')
        ) {
            return Result::SHOULD_BE_NULL;
        }

        return Result::KEEP_INITIALIZED;
    }
}
```

### Anonymous Function Processor (PHP 8.5+)

For projects running on PHP 8.5 or newer, the most convenient way to define your nullability logic is using a static anonymous function directly within the `#[NullableEmbeddable]` attribute. This keeps your business logic co-located with the embeddable definition, avoiding the need for separate processor classes.

**Example: Address Embeddable with Anonymous Function Processor**

Consider an `Address` embeddable that should be considered null if all its properties (`street`, `city`, `country`) are null.

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: static function (PropertyAccessor $propertyAccessor, object $embeddableObject): Result {
    if (!$embeddableObject instanceof Address) {
        throw UnexpectedEmbeddableClassException::create(Address::class, $embeddableObject);
    }
    if (
        null === $propertyAccessor->getValue($embeddableObject, 'street')
        && null === $propertyAccessor->getValue($embeddableObject, 'city')
        && null === $propertyAccessor->getValue($embeddableObject, 'country')
    ) {
        return Result::SHOULD_BE_NULL;
    }

    return Result::KEEP_INITIALIZED;
})]
class Address
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $city = null;

    #[ORM\Embedded(class: Country::class, columnPrefix: 'country_')]
    private ?Country $country = null;

    // ... getters and setters
}
```

In this example, the anonymous function receives a [`PropertyAccessor`](#the-propertyaccessor) and the `$embeddableObject`. The [`PropertyAccessor`](#the-propertyaccessor) is crucial as it allows you to safely check for uninitialized properties without triggering PHP fatal errors, even with `declare(strict_types=1)`. The function must return a [`Result`](#the-result-enum) enum (`Result::SHOULD_BE_NULL` or `Result::KEEP_INITIALIZED`).

**Example: Country Embeddable with Anonymous Function Processor**

A nested embeddable like `Country` can also use this approach. Here, `Country` is considered null if its `code` property is uninitialized (meaning it was never set, often indicating a new, empty object).

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: static function (PropertyAccessor $propertyAccessor, object $embeddableObject): Result {
    if (!$embeddableObject instanceof Country) {
        throw UnexpectedEmbeddableClassException::create(Country::class, $embeddableObject);
    }

    if ($propertyAccessor->isUninitialized($embeddableObject, 'code')) {
        return Result::SHOULD_BE_NULL;
    }

    return Result::KEEP_INITIALIZED;
})]
class Country
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 2, nullable: true)]
        private string $code,
    ) {
    }

    // ... getters and setters
}
```

### The `PropertyAccessor`

The `PropertyAccessor` provided to your processor (or anonymous function) is a specialized tool that allows you to inspect the state of embeddable properties, including whether they are uninitialized. This is particularly useful for non-nullable properties that might not have been set when an object is retrieved from the database or instantiated.

*   `$propertyAccessor->getValue($embeddableObject, 'propertyName')`: Safely retrieves the value of a property.
*   `$propertyAccessor->isUninitialized($embeddableObject, 'propertyName')`: Checks if a property is uninitialized.

### The `Result` Enum

The `analyze` method of your processor must return one of two values from the `Result` enum:

*   `Result::SHOULD_BE_NULL`: Indicates that the embeddable object should be treated as null. Note that "should" is used because the parent entity might have the embeddable class defined as not nullable. There is no guarantee the parent class accepts `null` as a value; this depends on database consistency and the user's data model.
*   `Result::KEEP_INITIALIZED`: Indicates that the embeddable object should remain initialized.

## Configuration

The bundle provides a configuration option to enable a cache warmer for improved performance in production environments.

```yaml
# config/packages/prod/andante_nullable_embeddable.yaml
andante_nullable_embeddable:
    metadata_cache_warmer_enabled: true
```

Alternatively, using PHP:

```php
<?php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if ('prod' === $containerConfigurator->env()) {
        $containerConfigurator->extension('andante_nullable_embeddable', [
            'metadata_cache_warmer_enabled' => true,
        ]);
    }
};
```

*   `metadata_cache_warmer_enabled` (default: `false`): When set to `true`, the bundle will read all `#[NullableEmbeddable]` attributes during Symfony's cache warmup process. This can speed up subsequent requests by pre-populating the metadata cache. It is recommended to enable this only in your production environment.

## Nested Embeddables

This bundle fully supports nested embeddables (e.g., an `Address` embeddable containing a `Country` embeddable). The processing logic correctly traverses the embeddable tree, starting from the deepest nested embeddable and working its way up to the root entity.

Built with love ❤️ by [AndanteProject](https://github.com/andanteproject) team.
