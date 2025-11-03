<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\Doctrine\EventSubscriber\NullableEmbeddableSubscriber;
use Andante\NullableEmbeddableBundle\Tests\App\AppKernel;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseFunctionalTest extends KernelTestCase
{
    protected ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ContainerInterface $container */
        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;

        // Create schema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata); // Drop existing schema
        $schemaTool->createSchema($metadata); // Create fresh schema
    }

    protected function tearDown(): void
    {
        if (null !== $this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        \assert(null !== $this->entityManager);

        return $this->entityManager;
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
