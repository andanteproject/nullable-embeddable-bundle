<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\CacheWarmer;

use Andante\NullableEmbeddableBundle\NullableEmbeddable\MetadataFactory;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Util\CacheKeyGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\AbstractPhpFileCacheWarmer;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class NullableEmbeddableCacheWarmer extends AbstractPhpFileCacheWarmer
{
    private MetadataFactory $nullableEmbeddableMetadataFactory;
    private ManagerRegistry $managerRegistry;

    public function __construct(
        MetadataFactory $metadataFactory,
        ManagerRegistry $managerRegistry,
        string $phpArrayFile,
    ) {
        parent::__construct($phpArrayFile);
        $this->nullableEmbeddableMetadataFactory = $metadataFactory;
        $this->managerRegistry = $managerRegistry;
    }

    protected function doWarmUp(string $cacheDir, ArrayAdapter $arrayAdapter, ?string $buildDir = null): bool
    {
        /** @var EntityManagerInterface $manager */
        foreach ($this->managerRegistry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                $cacheKey = CacheKeyGenerator::generateCacheKey($classMetadata->getName());
                $item = $arrayAdapter->getItem($cacheKey);
                $item->set($this->nullableEmbeddableMetadataFactory->create($classMetadata->getName()));
                $arrayAdapter->save($item);
            }
        }

        return true;
    }

    public function isOptional(): bool
    {
        return true;
    }
}
