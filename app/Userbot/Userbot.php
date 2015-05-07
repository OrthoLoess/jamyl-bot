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

    public function performUpdates()
    {
        $apiMonkey = new ApiMonkey($this);
        do {
            $charIds = User::listNeedUpdateIds(50);
            foreach ($charIds as $char) {
                $apiMonkey->addToAffiliationQueue($char);
            }
        } while (count($charIds));
        $apiMonkey->fireQueue(true);
    }

    public function updateAffiliations($phealResults)
    {
        foreach ($phealResults->characters->toArray() as $phealResult) {
            $phealResult['cachedUntil'] = $phealResults->cachedUntil;
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

    protected function getRandomBytes($nbBytes = 32)
    {
        $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
        if (false !== $bytes && true === $strong) {
            return $bytes;
        }
        else {
            throw new \Exception("Unable to generate secure token from OpenSSL.");
        }
    }

    function generatePassword($length){
        return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode($this->getRandomBytes($length+1))),0,$length);
    }
}