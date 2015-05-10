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
                if ($kill['zkb']['totalValue'] > config('killbot.min_value')) {
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
            if ($attacker['finalBlow'] == 1)
            {
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
                    'fallback'  => "Dank Frag ALERT!! ".$kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".number_format(round($kill['zkb']['totalValue']/1000000000.0, 2), 2)." bil -- ".config('killbot.kill_link').$kill['killID']."/",
                    'color'     => 'danger',
                    'title'     => $kill['victim']['characterName']." died in a ".$this->api->getTypeName($kill['victim']['shipTypeID'])." worth ".number_format(round($kill['zkb']['totalValue']/1000000000.0, 2), 2)." bil",
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
                    //'image_url' => config('killbot.ship_renders').$kill['victim']['shipTypeID']."_128.png",
                ],
            ],
        ];
        $this->slack->sendMessageToServer($payload);
        //var_dump($payload);
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

}