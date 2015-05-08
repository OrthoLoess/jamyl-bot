<?php
/**
 * Providence Slack user management bot
 * User: ed
 * Date: 01/05/15
 * Time: 09:34
 */

namespace JamylBot\Userbot;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JamylBot\User;

class Userbot {

    protected $apiMonkey;
    protected $slackMonkey;

    public function __construct()
    {
        $this->apiMonkey = new ApiMonkey($this);
        $this->slackMonkey = new SlackMonkey();
    }

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

    public function updateSingle($charId)
    {
        $this->apiMonkey->sendSingleAffiliation($charId);
    }

    public function updateAffiliations($phealResults)
    {
        foreach ($phealResults->characters->toArray() as $phealResult) {
            $phealResult['cachedUntil'] = $phealResults->cached_until;
            $user = User::findByChar($phealResult['characterID']);
            $user->updateAffiliation($phealResult);
        }
    }

    public function markAsErroring($charId, $error)
    {
        $user = User::findByChar($charId);
        $user->error = $error;
        $user->save();
    }

    public function clearError($charId)
    {
        $user = User::findByChar($charId);
        $user->error = null;
        $user->save();
    }

    public function addEmail($user, $email)
    {
        $this->slackMonkey->sendInvite($email, $user->char_name);
        $user->email = $email;
        $user->save();
    }

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
        /** @var User $users */
        $users = User::where('slack_id', null);
        $slackUsers = $this->slackMonkey->getUsers();
        foreach ($users as $user) {
            $user->searchSlackList($slackUsers);
        }
    }

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

    public static function generatePassword($length){
        return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(Userbot::getRandomBytes($length+1))),0,$length);
    }
}