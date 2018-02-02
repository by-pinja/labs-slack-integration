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
    public function sendAction(Request $request, Client $slackClient): JsonResponse
    {
        $text = $request->get('message');
        $output = true;

        if ($text !== null) {
            $message = $slackClient->createMessage();

            $message
                ->to('#' . $request->get('channel', 'labs'))
                ->from($request->get('nick', 'mörkö'))
                ->withIcon($request->get('icon', ':ghost:'))
                ->setText($text);
            try {
                $slackClient->sendMessage($message);
            } catch (\Exception $exception) {
                $output = false;
            }
        }

        return new JsonResponse($output, $output ? 200 : 500);
    }

    /**
     * @Route("")
     * @Method("POST")
     *
     * @param Request                $request
     * @param SerializerInterface    $serializer
     * @param IncomingMessageHandler $incomingMessageHandler
     *
     * @return JsonResponse
     */
    public function incomingAction(
        Request $request,
        SerializerInterface $serializer,
        IncomingMessageHandler $incomingMessageHandler
    ): JsonResponse {
        /** @var SlackIncomingWebHook $payload */
        $payload = $serializer->deserialize(\json_encode($request->request->all()), SlackIncomingWebHook::class, 'json');

        $incomingMessageHandler->process($payload);

        return new JsonResponse(true);
    }
}
