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

    /**
     * @param $charId
     *
     * @return User
     */
    public function findUserById($charId)
    {
        return User::where('char_id', $charId)->firstOrFail();
    }

    public function findUserBySlack($slackId)
    {
        return User::where('slack_id', $slackId)->firstOrFail();
    }

    public function updateAffiliation($phealResult)
    {
        $user = $this->findUserById($phealResult['characterID']);
        if ($user->corpId != $phealResult['corporationID'] || $user->allianceId != $phealResult['allianceID']){
            $user->corpId = $phealResult['corporationID'];
            $user->corpId = $phealResult['allianceID'];
            $user->save();
            $this->checkAccess($user);
        }
    }

    protected function checkAccess(User $user) {
        //
    }

    public function markAsErroring($charId, $error)
    {
        //
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