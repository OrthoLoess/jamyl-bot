<?php
/**
 * Providence Slack DankFrags bot
 * User: ed
 * Date: 08/05/15
 * Time: 23:27
 */

namespace JamylBot\Killbot;


use Illuminate\Support\Facades\DB;
use JamylBot\Userbot\SlackMonkey;
use JamylBot\Userbot\ApiMonkey;

class Killbot {

    protected $zkill;
    protected $slack;
    protected $api;

    public function __construct(ZkillMonkey $zkill, SlackMonkey $slack, ApiMonkey $api)
    {
        $this->zkill = $zkill;
        $this->slack = $slack;
        $this->api = $api;
    }

    public function cycleCorps()
    {
        foreach (config('killbot.corps') as $corp){
            if ( $corp['active'] === true ) {
                $this->getNewKills($corp);
            }
        }
    }

    protected function getNewKills($corp)
    {
        $kills = $this->zkill->pullCorpKills($corp['id'], $this->getLastId());
        if (count($kills)){
            $last = 0;
            foreach ($kills as $kill) {
                if ($kill['killID'] > $last) {
                    $last = $kill['killID'];
                }
                if (count($kill['attackers']) == 1) {
                    if ( 
                        !in_array($kill['victim']['shipTypeID'],config('killbot.capsule_type_ids')) || 
                        $kill['zkb']['totalValue'] > config('killbot.min_capsule_value') 
                    ) {
                        $this->sendSoloKill($kill, $corp);
                    }
                }
                elseif ($kill['zkb']['totalValue'] > config('killbot.min_value')) {
                    $this->sendKill($kill, $corp);
                }
            }
            if ($last)
                $this->saveLastId($last);
        }
    }
    protected function sendKill($kill, $corp) {
        $involved = "";
        $finalblow = "";
        foreach($kill['attackers'] as $attacker) {
            if ($attacker['corporationID'] == $corp['id']) {
                $involved .= $attacker['characterName']." (".$this->api->getTypeName($attacker['shipTypeID']).")\n";
            }
            if ($attacker['finalBlow'] == 1) {
                $finalblow = $attacker['characterName']." (".$this->api->getTypeName($attacker['shipTypeID']).")";
            }
        }
        $payload = [
            'username'  => config('killbot.name'),
            'channel'   => $corp['channel'],
            'icon_emoji'=> config('killbot.emoji'),
            'text' => '*Dank Frag Alert!!*',
            'attachments' => [
                [
                    'fallback'  => "Dank Frag ALERT!! ".$kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".$this->formatValue($kill['zkb']['totalValue'])." -- ".config('killbot.kill_link').$kill['killID']."/",
                    'color'     => 'danger',
                    'title'     => $kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".$this->formatValue($kill['zkb']['totalValue']),
                    'title_link'=> config('killbot.kill_link').$kill['killID']."/",
                    'fields'    => [
                        [
                            'title'     => 'Involved Corp Members',
                            'value'     => $involved,
                            'short'     => true
                        ],
                        [
                            'title'     => 'Final Blow',
                            'value'     => $finalblow,
                            'short'     => true
                        ],
                    ],
                    'thumb_url' => config('killbot.ship_renders').$kill['victim']['shipTypeID']."_256.png",
                ],
            ],
        ];
        $this->slack->sendMessageToServer($payload);
    }

    protected function sendSoloKill($kill, $corp) {
        $payload = [
            'username'   => config('killbot.name'),
            'channel'    => $corp['channel'],
            'icon_emoji' => config('killbot.emoji'),
            'text'       => '*Solo Kill!!*',
            'attachments'=> [
                [
                    'fallback'  => "Solo Kill!! ".$kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".$this->formatValue($kill['zkb']['totalValue'])." -- ".config('killbot.kill_link').$kill['killID']."/",
                    'color'     => 'danger',
                    'title'     => $kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".$this->formatValue($kill['zkb']['totalValue']),
                    'title_link'=> config('killbot.kill_link').$kill['killID']."/",
                    'fields'    => [
                        [
                            'title'     => 'Killer',
                            'value'     => $kill['attackers'][0]['characterName'],
                            'short'     => true
                        ],
                        [
                            'title'     => 'Using',
                            'value'     => $this->api->getTypeName($kill['attackers'][0]['shipTypeID']),
                            'short'     => true
                        ],
                    ],
                    'thumb_url' => config('killbot.ship_renders').$kill['victim']['shipTypeID']."_256.png",
                ],
            ],
        ];
        $this->slack->sendMessageToServer($payload);
    }

    protected function saveLastId($id)
    {
        if ($this->getLastId()) {
            DB::table('killbot')->where('id', 1)->update([
                'lastkill' => $id,
            ]);
        } else {
            DB::table('killbot')->insert([
                'id' => 1,
                'lastkill' => $id,
            ]);
        }
    }

    protected function getLastId()
    {
        $result = DB::table('killbot')->get();
        if (count($result)) {
            return $result[0]->lastkill;
        }
        return null;
    }

    public function resetLastId()
    {
        DB::table('killbot')->delete();
    }

    protected function formatValue($n)
    {
        if ( !is_numeric($n) )
            return 'Unknown ISK';
        
        if ( $n>1000000000000 )
            return number_format(round(($n/1000000000000),1), 1).' tril';

        else if ( $n>1000000000 ) 
            return number_format(round(($n/1000000000),1), 1).' bil';

        else if ( $n>100000000 ) 
            return number_format(round(($n/1000000),1), 1).' mil';
        
        return number_format($n). ' ISK';
    }

}