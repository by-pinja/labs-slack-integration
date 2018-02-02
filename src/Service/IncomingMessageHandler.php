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
     * Method to process all incoming Slack messages. This one will process all registered Slack message handlers and
     * check which one of those supports current message. If message is supported 'process' method of that class is
     * called - otherwise just skip to next handler.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        /**
         * Lambda function to check if incoming message from slack is supported by current handler or not. And if it is
         * supported current handler will process that message right away.
         *
         * @param HandlerInterface $handler
         */
        $iterator = function (HandlerInterface $handler) use ($slackIncomingWebHook): void {
            if ($handler->supports($slackIncomingWebHook)) {
                $handler->process($slackIncomingWebHook);
            }
        };

        $this->handlers->map($iterator);
    }
}
