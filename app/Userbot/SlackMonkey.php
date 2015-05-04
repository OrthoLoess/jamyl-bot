<?php
/**
 * Providence Slack user management bot - Slack API helper
 * User: ed
 * Date: 04/05/15
 * Time: 21:20
 */

namespace JamylBot\Userbot;


use GuzzleHttp\Client;

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

        $this->guzzle->get(config('pingbot.post-url'), ['json' => $payload]);
        return true;
    }

    /**
     * Get list of all users.
     *
     * @return Array
     */
    public function getUsers()
    {
        $response = $this->guzzle->get('users.list');
        if ($response->json()['ok'])
            return $response->json()['members'];
        //throw exception
        return false;
    }

    /**
     * Get user list for a channel
     *
     * @param String $channel
     *
     * @return bool
     */
    protected function getUsersForChannel($channel)
    {
        //

        return true;
    }

    /**
     * Get user list for a group
     *
     * @param String $group
     *
     * @return bool
     */
    protected function getUsersForGroup($group)
    {
        //

        return true;
    }

    /**
     * Invite the given user to the channel
     *
     * @param String $user
     * @param String $channel
     *
     * @return bool
     */
    protected function addToChannel($user, $channel)
    {
        //

        return true;
    }

    /**
     * Add the given user to the group
     *
     * @param String $user
     * @param String $group
     *
     * @return bool
     */
    protected function addToGroup($user, $group)
    {
        //

        return true;
    }

    /**
     * Kick the given user from the channel
     *
     * @param String $user
     * @param String $channel
     *
     * @return bool
     */
    protected function kickFromChannel($user, $channel)
    {
        //

        return true;
    }

    /**
     * Kick the given user from the group
     *
     * @param String $user
     * @param String $group
     *
     * @return bool
     */
    protected function kickFromGroup($user, $group)
    {
        //

        return true;
    }

}