<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\CacheClearer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class NullableEmbeddableCacheClearer implements CacheClearerInterface
{
    private Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function clear(string $cacheDir): void
    {
        $nullableEmbeddableMetadataCacheDir = $cacheDir.'/nullable_embeddable_metadata';
        $this->filesystem->remove($nullableEmbeddableMetadataCacheDir);
    }
}
