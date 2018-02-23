<?php
declare(strict_types = 1);
/**
 * /src/Handler/GitHubHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Helper\LoggerAwareTrait;
use App\Model\SlackIncomingWebHook;
use Github\Client as GitHubClient;
use Github\ResultPager;
use Nexy\Slack\Attachment;
use Nexy\Slack\AttachmentField;
use Nexy\Slack\Client as SlackClient;

/**
 * Class GitHubHandler
 *
 * @package App\Handler
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GitHubHandler implements HandlerInterface
{
    // Traits
    use HelperTrait;
    use LoggerAwareTrait;

    private const COMMAND_CHECK = 'check';

    /**
     * @var SlackClient
     */
    private $slackClient;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string[]
     */
    private $commands = [
        self::COMMAND_CHECK,
    ];

    /**
     * GitHubHandler constructor.
     *
     * @param SlackClient  $slackClient
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
        $message = [
            'check - Make basic repository checks to all repositories',
        ];

        return [
            $slackIncomingWebHook->getTriggerWord() . 'github',
            'GitHub - ' . $this->getSourceLink(\basename(__FILE__)) . "\n```" . \implode("\n", $message) . '```',
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
        preg_match('#^github (\w+)#u', $slackIncomingWebHook->getUserText(), $matches);

        if (\is_array($matches) && isset($matches[1]) && \in_array($matches[1], $this->commands, true)) {
            $this->command = $matches[1];
        }

        return $this->command !== null;
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
        switch ($this->command) {
            case self::COMMAND_CHECK:
                $this->processCheck($slackIncomingWebHook);
                break;
        }
    }

    /**
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @throws \Http\Client\Exception
     */
    private function processCheck(SlackIncomingWebHook $slackIncomingWebHook): void
    {
        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('GitHub')
            ->setIcon(':github:')
            ->setText('Started to check repositories on GitHub - _Please wait a while when I\'m doing heavy lifting..._');

        $this->slackClient->sendMessage($message);

        $client = new GitHubClient();
        $client->authenticate(\getenv('GITHUB_SECRET'), null, GitHubClient::AUTH_HTTP_TOKEN );

        $rateLimit = $client->api('rate_limit')->getRateLimits();

        if ($rateLimit['rate']['remaining'] < 500) {
            $dateTime = \DateTime::createFromFormat('U', (string)$rateLimit['rate']['reset']);
            $dateTime->setTimezone(new \DateTimeZone('Europe/Helsinki'));

            $date = $dateTime->format('d.m.Y H:i:s');

            $message = $this->slackClient->createMessage()
                ->to($slackIncomingWebHook->getChannelName())
                ->from('GitHub')
                ->setIcon(':github:')
                ->setText('_oh noes - GitHub rate limit reached - Sorry cannot continue atm - try again *' . $date . '*_');

            $this->slackClient->sendMessage($message);

            return;
        }

        $rep = [];

        $filter = function (array &$repository) use ($client, &$rep): bool {
            $output = true;

            try {
                $readme = $client->api('repo')->contents()->readme($repository['owner']['login'], $repository['name']);

                if ($readme['size'] < 100) {
                    $rep[$repository['full_name']] = 1;

                    throw new \LengthException('too small readme file - ' . $repository['full_name'] . ' - ' . $readme['size']);
                }

                $output = false;
            } catch (\Exception $exception) {
                if (!\array_key_exists($repository['full_name'], $rep)) {
                    $rep[$repository['full_name']] = 0;
                }

                $this->logger->info($exception->getMessage());
            }

            return $output;
        };


        $organizationApi = $client->api('organization');
        $repositories = (new ResultPager($client))->fetchAll($organizationApi, 'repositories', ['protacon']);

        $totalCountOfRepositories = \count($repositories);

        $repositories = \array_filter($repositories, $filter);

        $attachment = new Attachment();
        $attachment->setFooter('Please inform those repository owners / coders about this "problem"');

        $iterator = function (array $repository) use ($attachment, $rep): void {
            $message = '';

            if ($rep[$repository['full_name']] === 0) {
                $message = '*no README.md at all*';
            } elseif ($rep[$repository['full_name']] === 1) {
                $message = '_really short README.md_';
            }

            $field = new AttachmentField($repository['full_name'], $repository['html_url'] . "\n" . $message, true);

            $attachment->addField($field);
            $attachment->setColor('#ff0000');
        };

        \array_map($iterator, $repositories);

        $message = $this->slackClient->createMessage()
            ->to($slackIncomingWebHook->getChannelName())
            ->from('GitHub')
            ->setIcon(':github:')
            ->setText('Found total ' . \count($repositories) . '/' . $totalCountOfRepositories . ' repositories without *README.md* file OR it content is _really short_...')
            ->attach($attachment);

        $this->slackClient->sendMessage($message);
    }
}
