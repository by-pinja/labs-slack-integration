<?php
declare(strict_types = 1);
/**
 * /src/Controller/DefaultController.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Controller;

use App\Helper\LoggerAwareTrait;
use App\Model\SlackIncomingWebHook;
use App\Service\IncomingMessageHandler;
use App\Util\JSON;
use Nexy\Slack\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DefaultController
 *
 * @package App\Controller
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 *
 * @Route("/")
 */
class DefaultController
{
    // Traits
    use LoggerAwareTrait;

    /**
     * Method to process outgoing message from user input to specified Slack channel. Currently following parameters
     * are supported:
     *
     *  - message = Actual text message to send to channel (required)
     *  - channel = Channel name without # where to post message (optional, defaults to 'labs')
     *  - nick    = Nick name to use within this message (optional, defaults to 'mörkö')
     *  - icon    = Nick name icon to use within this message (optional, default to ':ghost:')
     *
     * @Route("")
     * @Method("GET")
     *
     * @param Request $request
     * @param Client  $slackClient
     *
     * @return JsonResponse
     *
     * @throws \Http\Client\Exception
     */
    public function outgoingAction(Request $request, Client $slackClient): JsonResponse
    {
        $text = $request->get('message');

        if ($text !== null) {
            $message = $slackClient->createMessage();

            $message
                ->to('#' . $request->get('channel', 'labs'))
                ->from($request->get('nick', 'mörkö'))
                ->withIcon($request->get('icon', ':ghost:'))
                ->setText($text);

            $slackClient->sendMessage($message);
        }

        return new JsonResponse();
    }

    /**
     * Action method to process incoming message from Slack. This action will convert request to proper object which
     * is validated before it's passed to actual incoming message handler.
     *
     * This main message handler can have multiple handler services attached to it. All services that implements
     * App\Handler\HandlerInterface interface are automatically added to main handler.
     *
     * @Route("")
     * @Method("POST")
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param IncomingMessageHandler $incomingMessageHandler
     *
     * @return JsonResponse
     * @throws \LogicException
     */
    public function incomingAction(
        Request $request,
        SerializerInterface $serializer,
        IncomingMessageHandler $incomingMessageHandler
    ): JsonResponse {
        /** @var SlackIncomingWebHook $payload */
        $payload = $serializer->deserialize(JSON::encode($request->request->all()), SlackIncomingWebHook::class, 'json');

        if ($payload->getToken() === \getenv('SLACK_TOKEN')) {
            $incomingMessageHandler->process($payload);
        } else {
            $this->logger->error('Invalid payload token!', $request->request->all());
        }

        return new JsonResponse();
    }
}
