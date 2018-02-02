<?php
declare(strict_types = 1);
/**
 * /src/Model/SlackIncomingWebhook.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Model;

/**
 * Class SlackIncomingWebhook
 *
 * @package App\Model
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class SlackIncomingWebHook
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $teamId;

    /**
     * @var string
     */
    private $teamDomain;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $channelId;

    /**
     * @var string
     */
    private $channelName;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $triggerWord;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return SlackIncomingWebHook
     */
    public function setToken(string $token): SlackIncomingWebHook
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * @param string $teamId
     *
     * @return SlackIncomingWebHook
     */
    public function setTeamId(string $teamId): SlackIncomingWebHook
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTeamDomain(): string
    {
        return $this->teamDomain;
    }

    /**
     * @param string $teamDomain
     *
     * @return SlackIncomingWebHook
     */
    public function setTeamDomain(string $teamDomain): SlackIncomingWebHook
    {
        $this->teamDomain = $teamDomain;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * @param string $serviceId
     *
     * @return SlackIncomingWebHook
     */
    public function setServiceId(string $serviceId): SlackIncomingWebHook
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @param string $channelId
     *
     * @return SlackIncomingWebHook
     */
    public function setChannelId(string $channelId): SlackIncomingWebHook
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     *
     * @return SlackIncomingWebHook
     */
    public function setChannelName(string $channelName): SlackIncomingWebHook
    {
        $this->channelName = $channelName;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     *
     * @return SlackIncomingWebHook
     */
    public function setTimestamp(string $timestamp): SlackIncomingWebHook
    {
        $this->timestamp = \DateTime::createFromFormat('U.u', $timestamp);

        return $this;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     *
     * @return SlackIncomingWebHook
     */
    public function setUserId(string $userId): SlackIncomingWebHook
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     *
     * @return SlackIncomingWebHook
     */
    public function setUserName(string $userName): SlackIncomingWebHook
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return SlackIncomingWebHook
     */
    public function setText(string $text): SlackIncomingWebHook
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getTriggerWord(): string
    {
        return $this->triggerWord;
    }

    /**
     * @param string $triggerWord
     *
     * @return SlackIncomingWebHook
     */
    public function setTriggerWord(string $triggerWord): SlackIncomingWebHook
    {
        $this->triggerWord = $triggerWord;

        return $this;
    }
}
