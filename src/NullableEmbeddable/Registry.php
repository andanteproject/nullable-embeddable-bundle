<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Util\CacheKeyGenerator;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class Registry
{
    private MetadataFactory $metadataFactory;
    /** @var array<class-string, Metadata> */
    private array $loadedMetadata = [];
    private PhpArrayAdapter $phpArrayAdapter;

    public function __construct(MetadataFactory $metadataFactory, PhpArrayAdapter $phpArrayAdapter)
    {
        $this->metadataFactory = $metadataFactory;
        $this->phpArrayAdapter = $phpArrayAdapter;
    }

    /**
     * @param class-string $entityClassFqcn
     *
     * @throws InvalidProcessorException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getNullableEmbeddableMetadata(string $entityClassFqcn): Metadata
    {
        if (isset($this->loadedMetadata[$entityClassFqcn])) {
            return $this->loadedMetadata[$entityClassFqcn];
        }

        $cacheKey = CacheKeyGenerator::generateCacheKey($entityClassFqcn);
        if ($this->phpArrayAdapter->hasItem($cacheKey)) {
            /** @var Metadata $cachedMetadata */
            $cachedMetadata = $this->phpArrayAdapter->getItem($cacheKey)->get();

            return $this->loadedMetadata[$entityClassFqcn] = $cachedMetadata;
        }

        // Fallback for development or if cache is not warmed
        return $this->loadedMetadata[$entityClassFqcn] = $this->metadataFactory->create($entityClassFqcn);
    }
}
