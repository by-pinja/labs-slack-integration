<?php
declare(strict_types = 1);
/**
 * /src/Handler/TravelHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Helper\LoggerAwareTrait;
use App\Model\SlackIncomingWebHook;
use GuzzleHttp\Client;
use Nexy\Slack\Client as SlackClient;

/**
 * Class TravelHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class TravelHandler implements HandlerInterface
{
    // Traits
    use LoggerAwareTrait;

    /**
     * @var SlackClient
     */
    private $slackClient;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $apiUri = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    /**
     * WeatherHandler constructor.
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
            '`%smatka [mista] [minne]` Kertoo välimatkan, keston sekä kilometrikorvaukset',
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
        \preg_match('#^matka (\w+) (\w+)$#u', $slackIncomingWebHook->getUserText(), $matches);

        if (\is_array($matches) && isset($matches[1])) {
            $this->from = $matches[1];
            $this->to = $matches[2];
        }

        return $this->from !== null;
    }

    /**
     * Method to process incoming message.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @throws \RuntimeException
     * @throws \Http\Client\Exception
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        $parameters = [
            'key'          => \getenv('GOOGLE_API_KEY'),
            'origins'      => $this->from,
            'destinations' => $this->to,
            'language'     => 'fi-FI',
            'units'        => 'metric',
        ];

        $client = new Client();

        $response = $client->get($this->apiUri . '?' . http_build_query($parameters));

        if ($response->getStatusCode() !== 200) {
            $this->logger->error($response->getBody()->getContents());

            return;
        }

        $data = \json_decode($response->getBody()->getContents());

        $text = \sprintf(
            'Välimatka %s - %s %s, kesto %s, kilometrikorvaukset %.2f€',
            $this->from,
            $this->to,
            $data->rows[0]->elements[0]->distance->text,
            $data->rows[0]->elements[0]->duration->text,
            $data->rows[0]->elements[0]->distance->value / 1000 * 0.42
        );

        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('Distance information')
            ->setIcon(':car:')
            ->setText($text);

        $this->slackClient->sendMessage($message);
    }
}