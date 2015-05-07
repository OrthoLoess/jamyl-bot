<?php
/**
 * Providence Slack user management bot
 * User: ed
 * Date: 01/05/15
 * Time: 09:34
 */

namespace JamylBot\Userbot;

use JamylBot\User;

class Userbot {

    protected $apiMonkey;

    public function __construct()
    {
        $this->apiMonkey = new ApiMonkey($this);
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