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
use Cache;

/**
 * Class ApiMonkey
 * @package JamylBot\Userbot
 */
class ApiMonkey {

    /**
     * @var Pheal
     */
    protected $pheal;
    /**
     * @var Userbot
     */
    protected $userbot;
    /**
     * @var array
     */
    protected $affiliationQueue=[];
    /**
     * @var int
     */
    protected $maxQueueSize = 50;

    /**
     * @param Userbot $userbot
     */
    function __construct(Userbot $userbot)
    {
        $this->pheal = new Pheal();
        $this->userbot = $userbot;
        if (config('eve.pheal_cache') == 'database') {
            $dbSettings = config('database.connections.mysql');
            PhealConfig::getInstance()->cache = new \Pheal\Cache\PdoStorage(
                $dbSettings['driver'] . ':host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['database'],
                $dbSettings['username'],
                $dbSettings['password']
            );
        } else {
            //PhealConfig::getInstance()->cache = new \Pheal\Cache\FileStorage(storage_path().'/app/phealCache/');
            PhealConfig::getInstance()->cache = new \Pheal\Cache\PredisStorage();
        }
        PhealConfig::getinstance()->access = new \Pheal\Access\StaticCheck();
    }

    /**
     * @param int $char
     * @param bool $fireNow
     */
    public function addToAffiliationQueue($char, $fireNow = false)
    {
        $this->affiliationQueue[] = $char;
        $this->fireQueue($fireNow);
    }

    /**
     * @return bool
     */
    protected function checkQueueLength()
    {
        return (count($this->affiliationQueue) >= $this->maxQueueSize);
    }

    /**
     * @param bool $force
     *
     * @return bool
     * @throws APIException
     * @throws \Exception
     */
    public function fireQueue($force = false)
    {
        if ($force || $this->checkQueueLength())
        {
            $this->sendQueuedCall();
            return true;
        }
        return false;
    }

    /**
     * @throws APIException
     * @throws \Exception
     */
    public function sendQueuedCall()
    {
        $charInfo = null;
        if (count($this->affiliationQueue) > 0)
        {
            try {
                $charInfo = $this->pheal->eveScope->CharacterAffiliation(['ids' => implode(',', $this->affiliationQueue)]);
                $this->userbot->updateAffiliations($charInfo);
                $this->affiliationQueue = [];
            } catch (APIException $e) {
                if ($e->code == 126) { // Invalid input found in ID list
                    foreach ($this->affiliationQueue as $charId)
                    {
                        try {
                            $this->sendSingleAffiliation($charId);
                        } catch (APIException $ee) {
                            if ($ee->code == 126) {
                                $this->userbot->markAsErroring($charId, $ee->code);
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

    /**
     * @param int $charId
     */
    public function sendSingleAffiliation($charId)
    {
        $result = $this->pheal->eveScope->CharacterAffiliation(['ids' => $charId]);
        $this->userbot->updateAffiliations($result);
    }

    /**
     * @param int $typeId
     */
    public function getTypeName($typeId)
    {
        $result = $this->pheal->eveScope->TypeName(['ids' => $typeId]);
        return $result->types[0]->typeName;
    }

    public function getCharName($id)
    {
        $result = $this->pheal->eveScope->CharacterAffiliation(['ids' => $id]);
        return $result->characters[0]->characterName;
    }

    /**
     * Look up corporation sheet to get ticker for given corp ID. Store in cache.
     *
     * @param $corpId
     * @return String
     */
    public function getCorpTicker($corpId)
    {
        return Cache::rememberForever("corpTicker:$corpId", function() use ($corpId) {
            $result = $this->pheal->corpScope->CorporationSheet(['corporationID' => $corpId]);
            return $result->ticker;
        });
    }

    /**
     * Look up given ID on the alliance list, store id->ticker in cache.
     *
     * @param $allianceId
     * @return String
     */
    public function getAllianceTicker($allianceId)
    {
        return Cache::rememberForever("allianceTicker:$allianceId", function() use ($allianceId) {
            $result = $this->pheal->eveScope->AllianceList(['version' => 1]);
            foreach ($result->alliances as $alliance){
                if ($alliance->allianceID == $allianceId){
                    return $alliance->shortName;
                }
            }
            throw new \Exception('Alliance not found');
        });
    }
}
