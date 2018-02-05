<?php
declare(strict_types = 1);
/**
 * /src/Handler/FridayHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Model\SlackIncomingWebHook;
use Nexy\Slack\Client as SlackClient;

/**
 * Class FridayHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class FridayHandler implements HandlerInterface
{
    // Traits
    use HelperTrait;

    /**
     * @var array
     */
    private static $links = [
        'http://www.riemurasia.net/video/Perjantai-video/156351',
        'http://www.riemurasia.net/kuva/Perjantai/153418',
        'http://www.riemurasia.net/kuva/Perjantai-jooga/149481',
        'http://koti.kapsi.fi/airair/arvaa/index.html',
        'http://party.toimii.fi/?autostart=true',
        'https://www.youtube.com/watch?v=2l0ueaJWPJY',
    ];

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
     * @return array
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): array
    {
        return [
            $slackIncomingWebHook->getTriggerWord() . 'perjantai',
            'Onko jo perjantai? - ' . $this->getSourceLink(\basename(__FILE__)),
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
        \preg_match('#^perjantai$#u', $slackIncomingWebHook->getUserText(), $matches);

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
        $text = 'Oh noes, eipä ole vielä perjantai...';
        $icon = ':disappointed:';
        $user = 'Party off';

        if ((int)\date('N') === 5) {
            $text = 'Jihaa kannat kattoon, nyt on PERJANTAI!!11! -' . self::$links[\array_rand(self::$links, 1)];
            $icon = ':partyparrot:';
            $user = 'Party on';
        }

        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from($user)
            ->setIcon($icon)
            ->setText($text);

        $this->slackClient->sendMessage($message);
    }
}
