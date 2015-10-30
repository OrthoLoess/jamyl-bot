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

    /**
     * Set the user's account to inactive. Can no longer log in or access anything.
     *
     * Undocumented API
     *
     * @param string $user
     *
     * @return bool
     * @throws SlackException
     */
    public function setInactive($user)
    {
        $response = $this->guzzle->post("users.admin.setInactive?user=$user&token=".config('slack.admin-token'));
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * Reactivate the user's account. Can be called on an already active account without erroring.
     *
     * Undocumented API
     *
     * @param string $user
     *
     * @return bool
     * @throws SlackException
     */
    public function setActive($user)
    {
        $response = $this->guzzle->post("users.admin.setRegular?user=$user&token=".config('slack.admin-token'));
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * Instruct slack to send an invite to the given email address. $channels is an array of channel ids the user
     * will auto-join.
     *
     * @param string $email
     * @param string $name
     * @param array $extraChannels
     *
     * @return bool
     * @throws SlackException
     */
    public function sendInvite($email, $name, $extraChannels = [])
    {
        /** @var array $channels */
        $channels = array_merge(config('slack.auto-join-channels'), $extraChannels);
        $response = $this->guzzle->post("users.admin.invite".
            "?email=".$email.
            "&first_name=".$name.
            "&token=".config('slack.admin-token').
            "&channels=".implode(',', $channels)
        );
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }

    /**
     * @param $userId
     *
     * @return mixed
     * @throws SlackException
     */
    public function getUserData($userId)
    {
        $response = $this->guzzle->get("users.info?user=$userId");
        if ($response->json()['ok'])
            return $response->json()['user'];
        throw new SlackException($response->json()['error']);
    }

    /**
     *
     * @return mixed
     * @throws SlackException
     */
    public function getGroupList()
    {
        return \Cache::remember('group-list', config('slack.cache-time'), function() {
            $response = $this->guzzle->get("groups.list?exclude_archived=1");
            if ($response->json()['ok'])
                return $response->json()['groups'];
            throw new SlackException($response->json()['error']);
        });
    }

    /**
     *
     * @return mixed
     * @throws SlackException
     */
    public function getChannelList()
    {
        return \Cache::remember('channel-list', config('slack.cache-time'), function() {
            $response = $this->guzzle->get("channels.list?exclude_archived=1");
            if ($response->json()['ok'])
                return $response->json()['channels'];
            throw new SlackException($response->json()['error']);
        });
    }

    /**
     * Set the user's name to the passed in string.
     *
     * Undocumented API
     *
     * @param string $user
     * @param string $firstName
     * @param string $lastName
     *
     * @return bool
     * @throws SlackException
     */
    public function setName($user, $firstName, $lastName = '')
    {
        $response = $this->guzzle->post("users.profile.set?user=$user&token=".config('slack.admin-token'), [
            'body' => 'profile='.urlencode('{"first_name":"'.$firstName.'","last_name":"'.$lastName.'"}'),
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);
        if ($response->json()['ok'])
            return true;
        throw new SlackException($response->json()['error']);
    }
}