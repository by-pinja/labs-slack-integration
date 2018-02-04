<?php
declare(strict_types = 1);
/**
 * /src/Handler/LannistusHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Model\SlackIncomingWebHook;
use Nexy\Slack\Client as SlackClient;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class LannistusHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class LannistusHandler implements HandlerInterface
{
    /**
     * @var SlackClient
     */
    private $slackClient;

    /**
     * FridayHandler constructor.
     *
     * @param SlackClient $slackClient
     */
    public function __construct(SlackClient $slackClient)
    {
        $this->slackClient = $slackClient;
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
        return sprintf(
            '`%slannistus` Päivän motivaattori',
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
        \preg_match('#^(lannistus)$#u', $slackIncomingWebHook->getUserText(), $matches);

        return \is_array($matches) && isset($matches[1]);
    }

    /**
     * Method to process incoming message.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Http\Client\Exception
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        $crawler = new Crawler(\file_get_contents('http://lannistajakuha.com/random'));

        $message = $this->slackClient->createMessage();
        $message->to($slackIncomingWebHook->getChannelName());
        $message->from('Motivaattori');
        $message->setIcon(':philosoraptor:');
        $message->setText($crawler->filter('div.lannistus p.teksti')->text());

        unset($crawler);

        $this->slackClient->sendMessage($message);
    }
}
