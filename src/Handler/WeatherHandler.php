<?php
declare(strict_types=1);
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
     * Method to check if handler supports incoming message or not.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @return bool
     */
    public function supports(SlackIncomingWebHook $slackIncomingWebHook): bool
    {
        preg_match('#^!sää (\w+)#u', $slackIncomingWebHook->getText(), $matches);

        if ($matches[1]) {
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
        // TODO handle this another way...
        $apiKey = 'bb93aef1f102fd80a1e46f6dc8a34ea6';

        $query = [
            'APPID' => $apiKey,
            'units' => 'metric',
            'lang'  => 'fi',
            'q'     => $this->location,
        ];

        $client = new Client();
        $response = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query($query));

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
        $message->from($slackIncomingWebHook->getUserName());
        $message->setText('@' . $slackIncomingWebHook->getUserName() . ' ' . $text);

        $this->slackClient->sendMessage($message);
    }
}
