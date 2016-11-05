<?php
/**
 * Providence Slack DankFrags Killboard pull
 * User: ed
 * Date: 08/05/15
 * Time: 23:01
 */

namespace JamylBot\Killbot;


use GuzzleHttp\Client;

/**
 * Class ZkillMonkey
 * @package JamylBot\Killbot
 */
class ZkillMonkey {

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     *
     */
    public function __construct()
    {
        $this->guzzle = new Client([
            'base_uri'  => config('killbot.base_url'),
            'defaults'  => [
                'headers'   => [
                    'Accept-Encoding' => 'gzip',
                ],
            ],

        ]);
    }

    /**
     *  Requests a JSON payload of kills from zKillboard
     *
     *  @param fetchMod Fetch modifier to use for the zKill kill API [https://github.com/zKillboard/zKillboard/wiki/API-(Killmails)#fetch-modifiers]
     *  @param fetchID  Fetch modifer value e.g corporation ID number
     *  @param afterID  Fetch kills after this kill ID
     *
     *  @returns JSON obj of kill data 
     */
    public function pullKills($fetchMod, $fetchID, $afterID = null)
    {
        $reqUrl = $fetchMod.'/'.$fetchID.'/';
        $reqUrl .= $afterID ? 'afterKillId/'.$afterID.'/' : '';
        $reqUrl .= 'orderDirection/desc/no-items/';

        $response = $this->guzzle->get($reqUrl);
        if ( $response->getStatusCode() != 200 ) {
            \Log::warning("Zkill returned HTTP status code: ".$response->getStatusCode()." when requesting kills for ".$fetchMod." - ".$fetchID);
        }

        return json_decode($response->getBody(), true);
    }

    public function pullCorpKills($corpId, $after = null)
    {
        return $this->pullKills('corporationID',$corpId, $after);
    }

}
