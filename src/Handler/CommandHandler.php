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
use Nexy\Slack\Attachment;
use Nexy\Slack\AttachmentField;
use Nexy\Slack\Client as SlackClient;

/**
 * Class CommandHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class CommandHandler implements HandlerInterface
{
    // Traits
    use HelperTrait;

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
     * @return array
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): array
    {
        return [
            $slackIncomingWebHook->getTriggerWord() . 'komennot',
            'Listaa käytettävissä olevat komennot - ' . $this->getSourceLink(\basename(__FILE__)),
        ];
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
        $attachment = new Attachment();

        /**
         * Lambda function to get each handler information for Slack message.
         *
         * @param HandlerInterface $handler
         *
         * @return AttachmentField
         */
        $iterator = function (HandlerInterface $handler) use ($slackIncomingWebHook): AttachmentField {
            [$title, $value] = $handler->getInformation($slackIncomingWebHook);

            return new AttachmentField($title, $value, true);
        };

        $sorter = function (AttachmentField $a, AttachmentField $b): int {
            return $a->getTitle() < $b->getTitle() ? -1 : 1;
        };

        $iterator = $this->incomingMessageHandler->get()->map($iterator)->getIterator();
        $iterator->uasort($sorter);

        /** @var AttachmentField $attachmentField */
        foreach ($iterator as $attachmentField) {
            $attachment->addField($attachmentField);
        }

        // Create message
        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('your humble servant')
            ->setIcon(':information_source:')
            ->attach($attachment);

        $this->slackClient->sendMessage($message);
    }
}
