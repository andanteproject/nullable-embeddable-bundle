<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\CacheClearer\NullableEmbeddableCacheClearer;
use Andante\NullableEmbeddableBundle\CacheWarmer\NullableEmbeddableCacheWarmer;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\MetadataFactory;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Registry;
use Andante\NullableEmbeddableBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ServiceContainerTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testServicesAreConfigured(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        // Test Registry service
        $registry = $container->get(Registry::class);
        $this->assertInstanceOf(Registry::class, $registry);

        // Test MetadataFactory service
        $metadataFactory = $container->get(MetadataFactory::class);
        $this->assertInstanceOf(MetadataFactory::class, $metadataFactory);

        // NullableEmbeddablePropertySubscriber is an event subscriber, not meant to be retrieved directly from the container.
        // Its functionality is tested implicitly by other functional tests.

        // Test CacheWarmer service
        $cacheWarmer = $container->get(NullableEmbeddableCacheWarmer::class);
        $this->assertInstanceOf(NullableEmbeddableCacheWarmer::class, $cacheWarmer);
        $this->assertInstanceOf(CacheWarmerInterface::class, $cacheWarmer);

        // Test CacheClearer service
        $cacheClearer = $container->get(NullableEmbeddableCacheClearer::class);
        $this->assertInstanceOf(NullableEmbeddableCacheClearer::class, $cacheClearer);
    }
}
