<?php
/**
 * /src/Kernel.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
declare(strict_types=1);

namespace App;

use App\Handler\HandlerInterface;
use App\Service\IncomingMessageHandler;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class Kernel
 *
 * @package App
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    private const CONFIG_EXTENSIONS = '.{php,xml,yaml,yml}';

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return \Generator|BundleInterface[] An array of bundle instances
     */
    public function registerBundles()
    {
        /** @noinspection PhpIncludeInspection */
        /** @noinspection UsingInclusionReturnValueInspection */
        /** @var array $contents */
        $contents = require $this->getProjectDir() . '/config/bundles.php';

        foreach ($contents as $class => $environments) {
            if (isset($environments['all']) || isset($environments[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * The extension point similar to the Bundle::build() method.
     *
     * Use this method to register compiler passes and manipulate the container during the building process.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(HandlerInterface::class)->addTag(HandlerInterface::class);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        $collection = $container->getDefinition(IncomingMessageHandler::class);

        foreach ($container->findTaggedServiceIds(HandlerInterface::class) as $id => $tags) {
            $collection->addMethodCall('set', [new Reference($id)]);
        }
    }

    /**
     * Configures the container.
     *
     * You can register extensions:
     *
     * $c->loadFromExtension('framework', array(
     *     'secret' => '%secret%'
     * ));
     *
     * Or services:
     *
     * $c->register('halloween', 'FooBundle\HalloweenProvider');
     *
     * Or parameters:
     *
     * $c->setParameter('halloween', 'lot of fun');
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws \Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/packages/*' . self::CONFIG_EXTENSIONS, 'glob');

        if (\is_dir($confDir . '/packages/' . $this->environment)) {
            $loader->load($confDir . '/packages/' . $this->environment . '/**/*' . self::CONFIG_EXTENSIONS, 'glob');
        }

        $loader->load($confDir . '/services' . self::CONFIG_EXTENSIONS, 'glob');
        $loader->load($confDir . '/services_' . $this->environment . self::CONFIG_EXTENSIONS, 'glob');
    }

    /**
     * Add or import routes into your application.
     *
     *     $routes->import('config/routing.yml');
     *     $routes->add('/admin', 'AppBundle:Admin:dashboard', 'admin_dashboard');
     *
     * @param RouteCollectionBuilder $routes
     *
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        if (\is_dir($confDir . '/routes/')) {
            $routes->import($confDir . '/routes/*' . self::CONFIG_EXTENSIONS, '/', 'glob');
        }

        if (\is_dir($confDir . '/routes/' . $this->environment)) {
            $routes->import($confDir . '/routes/' . $this->environment . '/**/*' . self::CONFIG_EXTENSIONS, '/', 'glob');
        }

        $routes->import($confDir . '/routes' . self::CONFIG_EXTENSIONS, '/', 'glob');
    }
}
