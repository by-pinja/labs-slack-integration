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
use Nexy\Slack\Attachment;
use Nexy\Slack\AttachmentField;
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
    use HelperTrait;
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
     * @return array
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): array
    {
        return [
            $slackIncomingWebHook->getTriggerWord() . 'matka [mista] [minne]',
            'Kertoo välimatkan, keston sekä kilometrikorvaukset - ' . $this->getSourceLink(\basename(__FILE__)),
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

        $title = $this->from . ' - ' . $this->to;

        $attachment = new Attachment();

        if ($data->rows[0]->elements[0]->status !== 'OK') {
            $value = 'oh noes, ei tuloksia...';

            $attachment->setColor('#ff0000');
        } else {
            $title .= ' (' . $data->origin_addresses[0] . ' - ' . $data->destination_addresses[0] . ')';

            $value = \sprintf(
                "Välimatka `%s`\nKesto `%s`\nKilometrikorvaukset `%.2f€`",
                $data->rows[0]->elements[0]->distance->text,
                $data->rows[0]->elements[0]->duration->text,
                $data->rows[0]->elements[0]->distance->value / 1000 * 0.42
            );

            $attachment->setFooter($this->getFooter($data));
        }

        $attachment->addField(new AttachmentField($title, $value));

        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('Distance information')
            ->setIcon(':car:')
            ->attach($attachment);

        $this->slackClient->sendMessage($message);
    }

    /**
     * @param \stdClass $data
     *
     * @return string
     */
    private function getFooter(\stdClass $data): string
    {
        return \sprintf(
            'Infot googlesta - <https://www.google.fi/maps/dir/%s/%s|katso reitti>',
            urlencode($data->origin_addresses[0]),
            $data->destination_addresses[0]
        );
    }
}