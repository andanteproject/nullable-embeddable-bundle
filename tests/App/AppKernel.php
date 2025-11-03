<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App;

use Andante\NullableEmbeddableBundle\AndanteNullableEmbeddableBundle;
use Andante\NullableEmbeddableBundle\Tests\App\Compiler\MakeServicesPublicCompilerPass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /** @var array<string, array<string, mixed>> */
    private array $config;

    /**
     * @param array<string, array<string, mixed>> $config
     */
    public function __construct(string $environment, bool $debug, array $config = [])
    {
        parent::__construct($environment, $debug);
        $this->config = $config;
    }

    /**
     * @return iterable<int, Bundle>
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new AndanteNullableEmbeddableBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.php');

        if (!empty($this->config)) {
            $loader->load(function (ContainerBuilder $container) {
                foreach ($this->config as $extension => $config) {
                    $container->loadFromExtension($extension, $config);
                }
            });
        }
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MakeServicesPublicCompilerPass());
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
