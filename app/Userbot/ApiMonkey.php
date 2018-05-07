<?php
/**
 * Providence Slack user management bot API helper
 * User: ed
 * Date: 01/05/15
 * Time: 09:37
 */

namespace JamylBot\Userbot;

use GuzzleHttp\Client;
use Pheal\Exceptions\APIException;
use Cache;

/**
 * Class ApiMonkey
 * @package JamylBot\Userbot
 */
class ApiMonkey {

    /**
     * @var Client
     */
    protected $guzzle;
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
        $this->guzzle = new Client([
            'base_uri'  => config('eve.esi-base'),
            'query'     => ['datasource' => 'tranquility'],
            'headers'   => [
                'User-Agent'    => 'JamylBot by Ortho Loess',
                'accept'        => 'application/json'
            ]
        ]);
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
                $charInfo = $this->guzzle->post(config('affiliation-route'), ['json' => $this->affiliationQueue]);
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
     * @param int $charIds
     * @return Array
     */
    public function sendAffiliations($charIds)
    {
        //echo config('eve.affiliation-route');
        $result = $this->guzzle->post(config('eve.affiliation-route'), ['json' => $charIds]);
        $resultArray = json_decode($result->getBody(), true);

        //echo 'Getting affiliations '.$result->getStatusCode()."\n";

        $char_ids = [];
        $corp_ids = [];
        $alliance_ids = [];
        foreach ($resultArray as &$character) {
            $char_ids[] = $character['character_id'];
            $corp_ids[] = $character['corporation_id'];
            if (isset($character['alliance_id'])) {
                $alliance_ids[] = $character['alliance_id'];
            }
        }
        //echo "char ids:\n";
        $this->cacheNames(array_values(array_unique($char_ids)));
        //dd(array_unique($corp_ids));
        //echo "corp ids:\n";
        $this->cacheNames(array_values(array_unique($corp_ids)));
        //echo "alliance ids:\n";
        $this->cacheNames(array_values(array_unique($alliance_ids)));

        foreach ($resultArray as &$character) {
            $character['character_name'] = $this->getName($character['character_id']);
            $character['corporation_name'] = $this->getName($character['corporation_id']);
            if (isset($character['alliance_id'])) {
                $character['alliance_name'] = $this->getName($character['alliance_id']);
            } else {
                $character['alliance_id'] = null;
                $character['alliance_name'] = null;
            }
        }

        return $resultArray;
    }

    /**
     * @param int $charId
     * @return Array
     */
    public function sendSingleAffiliation($charId)
    {
        $result = $this->sendAffiliations([$charId]);
        return $result[0];
    }

    /**
     * @param int $id
     * @return String
     */
    public function getName($id)
    {
        return Cache::rememberForever('esi-name-'.$id, function () use ($id) {
            $result = $this->guzzle->post(config('eve.names-route'), ['body' => '['.$id.']']);
            $resultArray = json_decode($result->getBody(), true);
            //echo 'Getting name for '.$id.' code: '.$result->getStatusCode()."\n";
            return $resultArray[0]['name'];
        });
    }

    /**
     * @param Array $ids
     */
    public function cacheNames($ids)
    {
        $result = $this->guzzle->post(config('eve.names-route'), ['json' => $ids]);
        $resultArray = json_decode($result->getBody(), true);
        //echo 'Getting names for id block: '.$result->getStatusCode()."\n";
        foreach ($resultArray as $nameRow) {
            Cache::forever('esi-name-'.$nameRow['id'], $nameRow['name']);
        }
    }

    /**
     * @param int $typeId
     */
    public function getTypeName($typeId)
    {
        $result = $this->pheal->eveScope->TypeName(['ids' => $typeId]);
        return $result->types[0]->typeName;
    }

    /**
     * @param int $id
     */
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
