<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\CacheWarmer\NullableEmbeddableCacheWarmer;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Registry;
use Andante\NullableEmbeddableBundle\Tests\App\AppKernel;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity\Order;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class CacheWarmerTest extends KernelTestCase
{
    private ?string $cacheDir = null;
    private ?Filesystem $filesystem = null;

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    /**
     * @param array<string, mixed>                $options
     * @param array<string, array<string, mixed>> $config
     */
    protected static function createKernel(array $options = [], array $config = []): KernelInterface
    {
        /** @var string $env */
        $env = $options['environment'] ?? 'test';

        return new AppKernel(
            environment: $env,
            debug: (bool) ($options['debug'] ?? true),
            config: $config
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->assertNotNull(self::$kernel); // PHPStan fix
        $this->cacheDir = self::$kernel->getCacheDir();
    }

    protected function getFileSystem(): Filesystem
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    protected function tearDown(): void
    {
        if (null !== $this->filesystem && null !== $this->cacheDir) {
            $this->getFileSystem()->remove($this->cacheDir.'/nullable_embeddable_metadata');
        }
        parent::tearDown();
    }

    public function testCacheWarmerPopulatesCache(): void
    {
        $container = self::getContainer();
        $registry = $container->get(Registry::class);
        $this->assertInstanceOf(Registry::class, $registry);

        /** @var NullableEmbeddableCacheWarmer $cacheWarmer */
        $cacheWarmer = $container->get(NullableEmbeddableCacheWarmer::class);

        /** @var string $cacheDir */
        $cacheDir = $container->getParameter('kernel.cache_dir');
        $this->assertNotNull($cacheDir);
        $cachedFiles = $cacheWarmer->warmUp($cacheDir);

        $this->assertNotEmpty($cachedFiles);
        $cacheFile = \sprintf('%s/nullable_embeddable_metadata.php', $cacheDir);
        $this->assertFileExists($cacheFile);

        $metadata = $registry->getNullableEmbeddableMetadata(Order::class);

        $this->assertNotNull($metadata);
        $this->assertSame(Order::class, $metadata->getEntityClassFqcn());
        $this->assertCount(4, $metadata->getEmbeds()); // billingAddress, billingAddress.country, shippingAddress, shippingAddress.country
    }

    public function testMetadataCacheWarmerEnabledConfiguration(): void
    {
        // Test with metadata_cache_warmer_enabled = false (default)
        $kernel = self::createKernel(['environment' => 'test', 'debug' => true], []);
        $cacheDir = $kernel->getCacheDir();
        $cacheFile = \sprintf('%s/nullable_embeddable_metadata.php', $cacheDir);
        $this->getFileSystem()->remove($cacheFile); // Ensure no previous cache file exists

        $kernel->boot(); // This should trigger cache warmers
        $this->assertFileDoesNotExist($cacheFile, 'Cache file should not exist when warmer is disabled.');
        $kernel->shutdown();

        // Test with metadata_cache_warmer_enabled = true
        $kernel = self::createKernel(['environment' => 'test', 'debug' => true], [
            'andante_nullable_embeddable' => [
                'metadata_cache_warmer_enabled' => true,
            ],
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        $cacheDir = $kernel->getCacheDir();
        $cacheFile = \sprintf('%s/nullable_embeddable_metadata.php', $cacheDir);
        $this->getFileSystem()->remove($cacheFile); // Ensure no previous cache file exists

        // Explicitly warm up the cache
        /** @var NullableEmbeddableCacheWarmer $nullableEmbeddableCacheWarmer */
        $nullableEmbeddableCacheWarmer = $container->get(NullableEmbeddableCacheWarmer::class);
        $nullableEmbeddableCacheWarmer->warmUp($cacheDir);

        $this->assertFileExists($cacheFile, 'Cache file should exist when warmer is enabled.');
        $kernel->shutdown();
    }
}
