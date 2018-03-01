<?php
/**
 * /src/DependencyInjection/Compiler/HandlerPass.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
declare(strict_types = 1);

namespace App\DependencyInjection\Compiler;

use App\Handler\HandlerInterface;
use App\Service\IncomingMessageHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class HandlerPass
 *
 * @package App\DependencyInjection\Compiler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class HandlerPass implements CompilerPassInterface
{
    /**
     * Within this compiler pass we're setting references to all implemented Slack message handlers to
     * IncomingMessageHandler so it can process each of those.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(HandlerInterface::class)->addTag(HandlerInterface::class);

        $collection = $container->getDefinition(IncomingMessageHandler::class);

        foreach ($container->findTaggedServiceIds(HandlerInterface::class) as $id => $tags) {
            $collection->addMethodCall('set', [new Reference($id)]);
        }
    }
}
