<?php
declare(strict_types = 1);
/**
 * /src/Handler/WeatherHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Helper\LoggerAwareTrait;
use App\Model\SlackIncomingWebHook;
use GuzzleHttp\Client;
use Nexy\Slack\Client as SlackClient;

/**
 * Class WeatherHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class WeatherHandler implements HandlerInterface
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
    private $location;

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
            '`%ssää [paikkakunta]` Kertoo sään halutulta paikkakunnalta',
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
        preg_match('#^sää (\w+)#u', $slackIncomingWebHook->getUserText(), $matches);

        if (\is_array($matches) && isset($matches[1])) {
            $this->location = $matches[1];
        }

        return $this->location !== null;
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
            'APPID' => \getenv('OPEN_WEATHER_MAP_API_KEY'),
            'units' => 'metric',
            'lang'  => 'fi',
            'q'     => $this->location,
        ];

        $client = new Client();
        $response = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query($parameters));

        if ($response->getStatusCode() !== 200) {
            $this->logger->error($response->getBody()->getContents());

            return;
        }

        $data = \json_decode($response->getBody()->getContents());

        $iterator = function (\stdClass $weather): string {
            return $weather->description;
        };

        $text = \sprintf(
            '%s°C %s, tuulta %sm/s',
            $data->main->temp,
            \implode(', ', \array_map($iterator, $data->weather)),
            $data->wind->speed
        );

        $message = $this->slackClient->createMessage();
        $message->to($slackIncomingWebHook->getChannelName());
        $message->from('Weather information');
        $message->setIcon($this->determineIcon($text));
        $message->setText($text);

        $this->slackClient->sendMessage($message);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function determineIcon(string $text): string
    {
        $icons = [
            ':sunny:'              => ['pouta', 'aurin', 'selkeä'],
            ':cloud:'              => ['pilvi'],
            ':zap:'                => ['ukkonen', 'myrsky'],
            ':snowflake:'          => ['lumi'],
            ':sweat_drops:'        => ['vesi'],
            ':new_moon_with_face:' => ['6666'], // Fallback :D
        ];

        foreach ($icons as $icon => $keywords) {
            \preg_match_all('#' . \implode('|', $keywords) . '#', $text, $matches);

            $icons[$icon] = \is_array($matches[0]) ? \count($matches[0]) : 0;
        }

        \asort($icons);

        $icons = \array_keys($icons);

        return \end($icons);
    }
}
