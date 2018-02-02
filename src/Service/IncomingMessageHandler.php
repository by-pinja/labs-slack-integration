<?php
declare(strict_types=1);
/**
 * /src/Service/IncomingMessageHandler.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Service;

use App\Handler\HandlerInterface;
use App\Model\SlackIncomingWebHook;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class IncomingMessageHandler
 *
 * @package App\Service
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class IncomingMessageHandler
{
    /**
     * @var ArrayCollection<IncomingMessageHandler>
     */
    private $handlers;

    /**
     * IncomingMessageHandler constructor.
     */
    public function __construct()
    {
        $this->handlers = new ArrayCollection();
    }

    /**
     * Setter method for IncomingMessageHandler handler.
     *
     * @param HandlerInterface $handler
     *
     * @return IncomingMessageHandler
     */
    public function set(HandlerInterface $handler): IncomingMessageHandler
    {
        $this->handlers->add($handler);

        return $this;
    }

    /**
     * @param SlackIncomingWebHook $slackIncomingWebHook
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        $iterator = function (HandlerInterface $handler) use ($slackIncomingWebHook) {
            if ($handler->supports($slackIncomingWebHook)) {
                $handler->process($slackIncomingWebHook);
            }
        };

        $this->handlers->map($iterator);
    }
}
