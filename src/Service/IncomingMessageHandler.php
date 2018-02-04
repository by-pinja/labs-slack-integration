<?php
declare(strict_types=1);
/**
 * /src/Service/IncomingMessageHandler.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Service;

use App\Handler\HandlerInterface;
use App\Helper\LoggerAwareTrait;
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
    // Traits
    use LoggerAwareTrait;

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
     * Getter method for configured handlers.
     *
     * @return ArrayCollection<HandlerInterface>
     */
    public function get(): ArrayCollection
    {
        return $this->handlers;
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
        $handled = false;

        /**
         * Lambda function to check if incoming message from slack is supported by current handler or not. And if it is
         * supported current handler will process that message right away.
         *
         * @param HandlerInterface $handler
         */
        $iterator = function (HandlerInterface $handler) use ($slackIncomingWebHook, &$handled): void {
            if ($handler->supports($slackIncomingWebHook)) {
                $message = \sprintf(
                    'Handling message \'%s\' with \'%s\' handler',
                    $slackIncomingWebHook->getUserText(),
                    \get_class($handler)
                );

                $this->logger->info($message);

                $handler->process($slackIncomingWebHook);

                $handled = true;
            }
        };

        $this->handlers->map($iterator);

        if ($handled === false) {
            $message = \sprintf(
                'No handler for message \'%s\'',
                $slackIncomingWebHook->getUserText()
            );

            $this->logger->error($message);
        }
    }
}
