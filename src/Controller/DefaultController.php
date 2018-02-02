<?php
declare(strict_types = 1);
/**
 * /src/Controller/DefaultController.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Controller;

use App\Model\SlackIncomingWebHook;
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
     * @param Request             $request
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    public function incomingAction(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $payload = $serializer->deserialize(\json_encode($request->request->all()), SlackIncomingWebHook::class, 'json');

        // TODO handle payloadl

        return new JsonResponse(true);
    }
}
