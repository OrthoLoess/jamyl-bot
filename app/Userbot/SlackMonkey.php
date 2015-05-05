<?php
/**
 * Providence Slack user management bot - Slack API helper
 * User: ed
 * Date: 04/05/15
 * Time: 21:20
 */

namespace JamylBot\Userbot;


use GuzzleHttp\Client;
use JamylBot\Exceptions\SlackException;

/**
 * Class SlackMonkey
 * @package JamylBot\Userbot
 */
class SlackMonkey {

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->guzzle = new Client([
            'base_url'  => config('slack.api-url'),
            'defaults'  => [
                'query'     => ['token' => config('slack.api-token')],
            ],
        ]);
    }

    /**
     * Takes the generated payload array and transmits it to slack. All the information needed to show the
     * message properly is in the payload array
     *
     * @param Array $payload
     *
     * @return bool
     */
    public function sendMessageToServer($payload)
    {
        $client = new Client();  // This function does not use the web API, so needs different defaults
        $client->get(config('pingbot.post-url'), ['json' => $payload]);
        return true;
    }

    /**
     * Get list of all users.
     * @return Array
     * @throws SlackException
     */
    public function getUsers()
    {
        $response = $this->guzzle->get('users.list');
        if ($response->json()['ok'])
            return $response->json()['members'];
        throw new SlackException($response->json()['error']);
    }

    /**
     * Get user list for a channel
     *
     * @param String $channel
     *
     * @return Array
     * @throws SlackException
     */
    public function getUsersForChannel($channel)
    {
        $response = $this->guzzle->get("channels.info?channel=$channel");
        if ($response->json()['ok'])
            return $response->json()['channel']['members'];
        throw new SlackException($response->json()['error']);
    }

    /**
     * Get user list for a group
     *
     * @param String $group
     *
     * @return Array
     * @throws SlackException
     */
    public function getUsersForGroup($group)
    {
        $response = $this->guzzle->get("groups.info?channel=$group");
        if ($response->json()['ok'])
            return $response->json()['group']['members'];
        throw new SlackException($response->json()['error']);
    }

    /**
     * Invite the given user to the channel
     *
     * @param String $user
     * @param String $channel
     *
     * @return bool
     * @throws SlackException
     */
    public function addToChannel($user, $channel)
    {
        $response = $this->guzzle->get("channels.invite?user=$user&channel=$channel");
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * Add the given user to the group
     *
     * @param String $user
     * @param String $group
     *
     * @return bool
     * @throws SlackException
     */
    public function addToGroup($user, $group)
    {
        $response = $this->guzzle->get("groups.invite?user=$user&channel=$group");
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * Kick the given user from the channel
     *
     * @param String $user
     * @param String $channel
     *
     * @return bool
     * @throws SlackException
     */
    public function kickFromChannel($user, $channel)
    {
        $response = $this->guzzle->get("channels.kick?user=$user&channel=$channel");
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * Kick the given user from the group
     *
     * @param String $user
     * @param String $group
     *
     * @return bool
     * @throws SlackException
     */
    public function kickFromGroup($user, $group)
    {
        $response = $this->guzzle->get("groups.kick?user=$user&channel=$group");
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

}