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
use Nexy\Slack\Attachment;
use Nexy\Slack\AttachmentField;
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
    use HelperTrait;
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
     * @return array
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): array
    {
        return [
            $slackIncomingWebHook->getTriggerWord() . 'sää [paikkakunta]',
            'Kertoo sään halutulta paikkakunnalta - ' . $this->getSourceLink(\basename(__FILE__)),
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

        $data = \json_decode($response->getBody()->getContents());

        $attachment = new Attachment();

        if ($response->getStatusCode() !== 200) {
            $this->logger->error($response->getBody()->getContents());

            $attachment->setColor('#ff0000');

            $value = '_oh noes ei data..._';
        } else {
            $value = $this->getAttachmentValue($data);
        }

        $attachment->addField(new AttachmentField($this->location, $value));

        $this->logger->error('https://api.openweathermap.org/data/2.5/weather?' . http_build_query($parameters));

        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('Weather information')
            ->setIcon($this->determineIcon($value))
            ->attach($attachment);
            //->setText($text);

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

    /**
     * @param $data
     *
     * @return string
     */
    private function getAttachmentValue(\stdClass $data): string
    {
        $iterator = function (\stdClass $weather): string {
            return \ucfirst($weather->description);
        };

        return \sprintf(
            "Lämpötila %s°C\n%s\nTuulta %s %sm/s (%.2f°)",
            $data->main->temp,
            \implode(', ', \array_map($iterator, $data->weather)),
            $this->windCardinals($data->wind->deg),
            $data->wind->speed,
            $data->wind->deg
        );
    }

    /**
     * @param float $deg
     *
     * @return string
     */
    private function windCardinals(float $deg): string
    {
        $cardinalDirections = [
            ['pohjoisesta',       348.75, 360],
            ['pohjoisesta',       0,      11.25],
            ['pohjoiskoilisesta', 11.25,  33.75],
            ['koilisesta',        33.75,  56.25],
            ['itäkoilisesta',     56.25,  78.75],
            ['idästä',            78.75,  101.25],
            ['itäkaakosta',       101.25, 123.75],
            ['kaakosta',          123.75, 146.25],
            ['eteläkaakosta',     146.25, 168.75],
            ['etelästä',          168.75, 191.25],
            ['etelälounaasta',    191.25, 213.75],
            ['lounaasta',         213.75, 236.25],
            ['länsilounaasta',    236.25, 258.75],
            ['lännestä',          258.75, 281.25],
            ['länsiluoteesta',    281.25, 303.75],
            ['luoteesta',         303.75, 326.25],
            ['pohjoisluoteesta',  326.25, 348.75],
        ];

        foreach ($cardinalDirections as $angles) {
            if ($deg >= $angles[1] && $deg < $angles[2]) {
                $cardinal = $angles[0];
            }
        }

        return $cardinal;
    }
}
