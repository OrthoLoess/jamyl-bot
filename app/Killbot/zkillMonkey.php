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
 * Class zkillMonkey
 * @package JamylBot\Killbot
 */
class zkillMonkey {

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @param Client $guzzle
     */
    public function __construct()
    {
        $this->guzzle = new Client([
            'base_url'  => 'https://zkillboard.com/api/kills/',
            'headers'   => [
                'Accept-Encoding' => 'gzip',
            ],
        ]);
    }

    public function pullCorpKills($corpId, $after = null)
    {
        $path = 'corporationID/'.$corpId.'/';
        if ($after) {
            $path .= 'afterKillId/'.$after.'/';
        }
        $response = $this->guzzle->get($path);
        return $response->json();
    }

}