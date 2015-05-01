<?php
/**
 * Providence Slack user management bot API helper
 * User: ed
 * Date: 01/05/15
 * Time: 09:37
 */

namespace JamylBot\Userbot;


use Pheal\Exceptions\APIException;
use Pheal\Pheal;
use Pheal\Core\Config as PhealConfig;

/**
 * Class ApiMonkey
 * @package JamylBot\Userbot
 */
class ApiMonkey {

    protected $pheal;
    protected $userbot;
    protected $affiliationQueue=[];
    protected $maxQueueSize = 50;

    /**
     * @param Pheal $pheal
     * @param Userbot $userbot
     */
    function __construct(Pheal $pheal, Userbot $userbot)
    {
        $this->pheal = $pheal;
        $this->userbot = $userbot;
        PhealConfig::getInstance()->cache = new \Pheal\Cache\FileStorage(storage_path().'/app/phealCache/');
        PhealConfig::getinstance()->access = new \Pheal\Access\StaticCheck();
    }

    /**
     * Take characterID, look up affiliation in API and check against standings.
     *
     * @param $char
     *
     * @return array
     */
    public function checkCharacter($char)
    {
        return $this->affiliation($char);
    }

    /**
     * Pass $charID to affiliation endpoint, return corp and alliance info.
     *
     * @param $char
     *
     * @return array
     */
    protected function affiliation($char)
    {
        $charInfo = $this->pheal->eveScope->CharacterAffiliation(['ids' => $char]);

        dd($charInfo->characters->toArray());
        return $charInfo;
    }

    public function addToAffiliationQueue($char)
    {
        $this->affiliationQueue[] = $char;
    }

    protected function checkQueueLength()
    {
        return (count($this->affiliationQueue) >= $this->maxQueueSize);
    }

    protected function fireQueue($force = false)
    {
        if ($force || $this->checkQueueLength())
        {
            return true;
        }
        return false;
    }

    public function sendQueuedCall()
    {
        $charInfo = null;
        if (count($this->affiliationQueue) > 0)
        {
            try {
                $charInfo = $this->pheal->eveScope->CharacterAffiliation(['ids' => implode(',', $this->affiliationQueue)]);
                $this->handleMultipleAffiliations($charInfo->characters->toArray());
                $this->affiliationQueue = [];
            } catch (APIException $e) {
                if ($e->code == 126) { // Invalid input found in ID list
                    foreach ($this->affiliationQueue as $charId)
                    {
                        try {
                            $this->sendSingleAffiliation($charId);
                        } catch (APIException $ee) {
                            if ($ee->code == 126) {
                                $this->userbot->markAsErroring($charId, $ee);
                            } else {
                                throw $ee;
                            }
                        }
                    }
                    $this->affiliationQueue = [];
                } else {
                    throw $e;
                }
            }
        }
    }

    protected function sendSingleAffiliation($charId)
    {
        $result = $this->pheal->eveScope->CharacterAffiliation(['ids' => $charId]);

        $this->userbot->updateAffiliation($result->characters->toArray()[0]);
    }

    protected function handleMultipleAffiliations($resultArray)
    {
        foreach ($resultArray as $result) {
            $this->userbot->updateAffiliation($result);
        }
    }
}
