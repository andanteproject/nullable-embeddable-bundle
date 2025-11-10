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

abstract class AppKernel extends Kernel
{
    /** @var array<string, array<string, mixed>> */
    protected array $config;

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

        if (Kernel::VERSION_ID >= 60100) { // Symfony 6.1.0 or higher
            $loader->load(function (ContainerBuilder $container) {
                $frameworkConfig = [];
                if (Kernel::VERSION_ID >= 60100) { // Symfony 6.1.0 or higher
                    $frameworkConfig['http_method_override'] = false;
                }
                if (Kernel::VERSION_ID >= 60400) { // Symfony 6.4.0 or higher
                    $frameworkConfig['handle_all_throwables'] = true;
                    $frameworkConfig['php_errors'] = ['log' => true];
                }
                if (Kernel::VERSION_ID >= 70300) { // Symfony 7.3.0 or higher
                    $frameworkConfig['property_info'] = ['with_constructor_extractor' => true];
                    $container->loadFromExtension('doctrine', [
                        'orm' => [
                            'enable_native_lazy_objects' => true,
                        ],
                    ]);
                }
                $container->loadFromExtension('framework', $frameworkConfig);
            });
        }

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
