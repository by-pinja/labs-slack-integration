<?php
declare(strict_types=1);
/**
 * /src/Handler/HandlerInterface.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

use App\Model\SlackIncomingWebHook;

/**
 * Interface HandlerInterface
 *
 * @package App\Handler
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface HandlerInterface
{
    /**
     * Method to get handler information.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @return array
     */
    public function getInformation(SlackIncomingWebHook $slackIncomingWebHook): array;

    /**
     * Method to check if handler supports incoming message or not.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     *
     * @return bool
     */
    public function supports(SlackIncomingWebHook $slackIncomingWebHook): bool;

    /**
     * Method to process incoming message.
     *
     * @param SlackIncomingWebHook $slackIncomingWebHook
     */
    public function process(SlackIncomingWebHook $slackIncomingWebHook): void;
}
