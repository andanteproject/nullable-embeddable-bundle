# Nullable Embeddable Bundle
#### Symfony Bundle - [AndanteProject](https://github.com/andanteproject)

A Symfony Bundle to handle nullable embeddables with Doctrine.

## Requirements
Symfony 5.x-7.x and PHP 8.2.

## Install
Via [Composer](https://getcomposer.org/):
```bash
$ composer require andanteproject/nullable-embeddable-bundle
```

## Features
- Automatically handles nullable embeddables in Doctrine entities.

## Basic usage
After [install](#install), make sure you have the bundle registered in your symfony bundles list (`config/bundles.php`):
```php
return [
    /// bundles...
    Andante\NullableEmbeddableBundle\AndanteNullableEmbeddableBundle::class => ['all' => true],
    /// bundles...
];
```
This should have been done automagically if you are using [Symfony Flex](https://flex.symfony.com). Otherwise, just register it by yourself.

Built with love ❤️ by [AndanteProject](https://github.com/andanteproject) team.
