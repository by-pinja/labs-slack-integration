<?php
declare(strict_types = 1);
/**
 * /src/Handler/FridayHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Model\SlackIncomingWebHook;
use App\Service\IncomingMessageHandler;
use Nexy\Slack\Client as SlackClient;

/**
 * Class CommandHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class CommandHandler implements HandlerInterface
{
    /**
     * @var SlackClient
     */
    private $slackClient;

    /**
     * @var IncomingMessageHandler
     */
    private $incomingMessageHandler;

    /**
     * FridayHandler constructor.
     *
     * @param SlackClient            $slackClient
     * @param IncomingMessageHandler $incomingMessageHandler
     */
    public function __construct(SlackClient $slackClient, IncomingMessageHandler $incomingMessageHandler)
    {
        $this->slackClient = $slackClient;
        $this->incomingMessageHandler = $incomingMessageHandler;
    }

    /**
     * Method to get handler information.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @return string
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): string
    {
        return \sprintf(
            '`%skomennot` Listaa käytettävissä olevat komennot',
            $slackIncomingWebHook->getTriggerWord()
        );
    }

    /**
     * Method to check if handler supports incoming message or not.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @return bool
     */
    public function supports(SlackIncomingWebHook $slackIncomingWebHook): bool
    {
        \preg_match('#^komennot$#u', $slackIncomingWebHook->getUserText(), $matches);

        return \is_array($matches) && isset($matches[0]);
    }

    /**
     * Method to process incoming message.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @throws \Http\Client\Exception
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        /**
         * Lambda function to get each handler information for Slack message.
         *
         * @param HandlerInterface $handler
         *
         * @return string
         */
        $iterator = function (HandlerInterface $handler) use ($slackIncomingWebHook): string {
            return $handler->getInformation($slackIncomingWebHook);
        };

        $commands = $this->incomingMessageHandler->get()->map($iterator)->toArray();

        sort($commands);

        // Create message
        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('your humble servant')
            ->setIcon(':information_source:')
            ->setText(\implode("\n", $commands));

        $this->slackClient->sendMessage($message);
    }
}
