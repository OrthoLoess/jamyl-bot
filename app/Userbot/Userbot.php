<?php
/**
 * Providence Slack user management bot
 * User: ed
 * Date: 01/05/15
 * Time: 09:34
 */

namespace JamylBot\Userbot;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use JamylBot\Channel;
use JamylBot\Exceptions\SlackException;
use JamylBot\User;

/**
 * Class Userbot
 * @package JamylBot\Userbot
 */
class Userbot {

    /**
     * @var ApiMonkey
     */
    public $apiMonkey;
    /**
     * @var SlackMonkey
     */
    public $slackMonkey;

    /**
     *
     */
    public function __construct()
    {
        $this->apiMonkey = new ApiMonkey($this);
        $this->slackMonkey = new SlackMonkey();
    }

    /**
     *
     */
    public function performUpdates()
    {
        do {
            $charIds = User::listNeedUpdateIds(50);
            foreach ($charIds as $char) {
                $this->apiMonkey->addToAffiliationQueue($char);
            }
            $this->apiMonkey->fireQueue(true);
        } while (count($charIds));
    }

    /**
     * @param $charId
     */
    public function updateSingle($charId)
    {
        $this->apiMonkey->sendSingleAffiliation($charId);
    }

    /**
     * @param $phealResults
     */
    public function updateAffiliations($phealResults)
    {
        foreach ($phealResults->characters->toArray() as $phealResult) {
            $phealResult['cachedUntil'] = $phealResults->cached_until;
            $user = User::findByChar($phealResult['characterID']);
            $user->updateAffiliation($phealResult);
        }
    }

    /**
     * @param $charId
     * @param $error
     */
    public function markAsErroring($charId, $error)
    {
        $user = User::findByChar($charId);
        $user->error = $error;
        $user->save();
    }

    /**
     * @param $charId
     */
    public function clearError($charId)
    {
        $user = User::findByChar($charId);
        $user->error = null;
        $user->save();
    }

    /**
     * @param $user
     * @param $email
     *
     * @throws \JamylBot\Exceptions\SlackException
     */
    public function addEmail($user, $email)
    {
        try {
            $this->slackMonkey->sendInvite($email, $user->char_name);
        } catch (SlackException $e) {
            if ($e->getMessage() == 'already_in_team') {
                $user->email = $email;
                $user->save();
                $this->linkSlackMembers();
            } else {
                throw $e;
            }
        }
        $user->email = $email;
        $user->save();
    }

    /**
     * @param $requestVars
     *
     * @return string
     * @throws \JamylBot\Exceptions\SlackException
     */
    public function registerSlack($requestVars)
    {
        if ($requestVars['token'] != config('slack.register-token')) {
            return 'Invalid authentication token';
        }
        $userData = $this->slackMonkey->getUserData($requestVars['user_id']);
        try {
            $user = User::findByEmail($userData['profile']['email']);
        } catch ( ModelNotFoundException $e ) {
            try {
                $user = User::findByChar($requestVars['text']);
                $user->email = $userData['profile']['email'];
            } catch ( ModelNotFoundException $ee ) {
                return "Character not registered on management system.";
            }
        }
        if ($user->slack_id)
            return 'User already registered';
        $user->slack_id = $userData['id'];
        $user->slack_name = $userData['name'];
        $user->save();
        return 'User details updated.';
    }

    /**
     * @throws \JamylBot\Exceptions\SlackException
     */
    public function linkSlackMembers()
    {
        /** @var array $users */
        $users = User::where('slack_id', null)->get();
        //\Log::info('Users: '.count($users));
        $slackUsers = $this->slackMonkey->getUsers();
        foreach ($users as $user) {
            $user->searchSlackList($slackUsers);
        }
    }

    /**
     * Deletes any user who has not entered an email address. Only affects accounts that were created more than
     * $hours hours ago. Default is 48 hours.
     *
     * @param int $hours
     */
    public function clearNoEmail($hours = 48)
    {
        User::where('email', null)->where('created_at', '<', Carbon::now()->addHours($hours))->delete();
    }

    /**
     * @return array
     */
    public function listUnregistered()
    {
        $users = User::where('slack_id', null)->get();
        return $users;
    }

    /**
     *
     */
    public function setSlackInactives()
    {
        $users = User::all();
        foreach ($users as $user){
            if ($user->slack_id !== null && !$user->inactive &&
                !($user->status == 'holder' || $user->status == 'blue' || $user -> status == 'light-blue')) {
                try {
                    $this->slackMonkey->setInactive($user->slack_id);
                    $user->inactive = true;
                    $user->save();
                    \Log::info('User '.$user->char_name.' set inactive on slack API');
                } catch (SlackException $e ){
                    \Log::error($e->getMessage());
                }
            }
            if ($user->slack_id !== null && $user->inactive &&
                ($user->status == 'holder' || $user->status == 'blue' || $user -> status == 'light-blue')) {
                try {
                    $this->slackMonkey->setActive($user->slack_id);
                    $user->inactive = false;
                    $user->save();
                    \Log::info('User '.$user->char_name.' reset to ACTIVE on slack API');
                } catch (SlackException $e ){
                    \Log::error($e->getMessage());
                }
            }
        }
    }

    /**
     * Resets all statuses, based on standings, without doing API check first. can be triggered after updating
     * standings to force a refresh.
     */
    public function readNewStandings()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->updateStatus();
            $user->save();
        }
    }

    /**
     *
     */
    public function getNewChannels()
    {
        $channels = $this->slackMonkey->getChannelList();
        foreach ($channels as $channel) {
            Channel::firstOrCreate([
                'name' => $channel['name'],
                'slack_id' => $channel['id'],
                'is_group' => false,
            ]);
        }
        $groups = $this->slackMonkey->getGroupList();
        foreach ($groups as $group) {
            Channel::firstOrCreate([
                'name' => $group['name'],
                'slack_id' => $group['id'],
                'is_group' => true,
            ]);
        }
    }

    /**
     * @param $channel
     * @throws SlackException
     */
    public function manageChannel($channel)
    {
        $channelIds = [];
        $channelUsers = $channel->is_group ? $this->slackMonkey->getUsersForGroup($channel->slack_id) : $this->slackMonkey->getUsersForChannel($channel->slack_id);
        foreach ($channelUsers as $user) {
            $channelIds[] = $user;
            $hasAccess = false;
            foreach ($channel->groups as $group) {
                /** @var \JamylBot\Group $group */
                if ( $group->isMemberBySlack($user) ) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess && $user != config('slack.jamyl-id')) {
                $channel->is_group ? $this->slackMonkey->kickFromGroup($user, $channel->slack_id) : $this->slackMonkey->kickFromChannel($user, $channel->slack_id);
            }
        }
        foreach ($channel->groups as $group) {
            foreach ($group->users as $jamylUser) {
                if (!in_array($jamylUser->slack_id, $channelIds) && $group->isMemberBySlack($jamylUser->slack_id)) {
                    $channel->is_group ? $this->slackMonkey->addToGroup($jamylUser->slack_id, $channel->slack_id) : $this->slackMonkey->addToChannel($jamylUser->slack_id, $channel->slack_id);
                }
            }
        }
    }

    /** DISABLED - slack is rate limiting too heavily to make this feasible
     *
     *
    public function checkNames()
    {
        $slackUsers = $this->slackMonkey->getUsers();
        foreach ($slackUsers as $slackUser) {
            //print($slackUser['real_name']);
            if (!$slackUser['deleted'] && !$slackUser['is_admin'] && !$slackUser['is_bot']) {
                $this->compareName($slackUser['real_name'], $slackUser['id']);
            }
        }
    }

    /**
     * @param $slackName
     * @param $slackId
     * @throws SlackException
     *
    private function compareName($slackName, $slackId)
    {
        try {
            $user = User::findBySlack($slackId);
            if ($user->getDisplayName(true) != $slackName) {
                \Log::notice("Changing $slackName to ".$user->getDisplayName(true));
                $newName = $user->getDisplayName();
                $this->slackMonkey->setName($slackId, $newName['first'], $newName['last']);
            }
        } catch (ModelNotFoundException $e) {
            // TODO: Disable user?
            \Log::notice("Slack user $slackId : $slackName not found on jamylbot");
        }
    }
*/
    /**
     * @param int $nbBytes
     *
     * @return string
     * @throws \Exception
     */
    protected static function getRandomBytes($nbBytes = 32)
    {
        $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
        if (false !== $bytes && true === $strong) {
            return $bytes;
        }
        else {
            throw new \Exception("Unable to generate secure token from OpenSSL.");
        }
    }

    /**
     * @param $length
     *
     * @return string
     * @throws \Exception
     */
    public static function generatePassword($length){
        return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(Userbot::getRandomBytes($length+1))),0,$length);
    }

}