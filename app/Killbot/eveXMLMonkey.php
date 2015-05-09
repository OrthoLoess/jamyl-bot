<?php
/**
 * Providence Slack DankFrags XML API pull
 * User: prozn
 * Date: 09/05/15
 * Time: 19:32
 */

namespace JamylBot\Killbot;


use GuzzleHttp\Client;

/**
 * Class eveXMLMonkey
 * @package JamylBot\Killbot
 */
class eveXMLMonkey {

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     *
     */
    public function __construct()
    {
        $this->guzzle = new Client();
    }

    public function getItemNameFromID($id)
    {
        if(is_numeric($id)) {
            $response = $this->guzzle->get(config('killbot.typename_link'), [
                'query' => ['ids' => $id]
            ]);
            $xml = $response->xml();
            return $xml->result->rowset->row['typeName'];
            //var_dump($xml);
            //return true;
        }
        return false;
    }

}