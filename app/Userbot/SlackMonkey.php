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
use Cache;

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
            'base_uri'  => config('slack.api-url'),
            'query'     => ['token' => config('slack.api-token')],
        ]);
    }

    /**
     * Takes the generated payload array and transmits it to slack. All the information needed to show the
     * message properly is in the payload array
     *
     * @param Array $payload
     * @return bool
     * @throws SlackException
     */
    public function sendMessageToServer($payload)
    {
        //$client = new Client();  // This function does not use the web API, so needs different defaults
        //$client->get(config('pingbot.post-url'), ['json' => $payload]);
        if (array_key_exists('attachments', $payload)) {
            $payload['attachments'] = json_encode($payload['attachments']);
        }
        $response = $this->guzzle->post('chat.postMessage', ['query' => array_merge($this->guzzle->getConfig('query'), $payload)]);
        $array = json_decode($response->getBody(), true);
        if ($array['ok'])
            return true;
        throw new SlackException($array['error']);
    }

    /**
     * Get list of all users.
     * @return Array
     * @throws SlackException
     */
    public function getUsers()
    {
        return Cache::remember('slack_user_list', 3, function() {
            $response = $this->guzzle->get('users.list');
            if (json_decode($response->getBody(), true)['ok']) {
                $members = json_decode($response->getBody(), true)['members'];
                array_pop($members);
                return $members;
            }
            throw new SlackException(json_decode($response->getBody(), true)['error']);
        });
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
        if (json_decode($response->getBody(), true))
            return $response->json()['channel']['members'];
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return $response->json()['group']['members'];
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        \Log::error('Exception thrown on '.$user.' when adding to '.$group);
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return true;
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
        if (json_decode($response->getBody(), true)['ok'])
            return $response->json()['user'];
        throw new SlackException(json_decode($response->getBody(), true)['error']);
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
            if (json_decode($response->getBody(), true)['ok'])
                return $response->json()['groups'];
            throw new SlackException(json_decode($response->getBody(), true)['error']);
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
            if (json_decode($response->getBody(), true)['ok'])
                return $response->json()['channels'];
            throw new SlackException(json_decode($response->getBody(), true)['error']);
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
    /*
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
    */
}
