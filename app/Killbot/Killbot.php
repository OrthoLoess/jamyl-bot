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

class Killbot {

    protected $zkill;
    protected $slack;

    public function __construct(ZkillMonkey $zkill, SlackMonkey $slack)
    {
        $this->zkill = $zkill;
        $this->slack = $slack;
    }

    public function cycleCorps()
    {
        foreach (config('killbot.corps') as $corp){
            $this->getNewKills($corp);
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
        $payload = [
            'username'  => config('killbot.name'),
            'channel'   => $corp['channel'],
            'icon_emoji'=> config('killbot.emoji'),
            'text'      => "Dank Frag ALERT!!\n".
                $kill['victim']['characterName']." died in a ship worth ".number_format(round($kill['zkb']['totalValue']/1000000000.0, 2), 2)." bil\n".
                config('killbot.kill_link').$kill['killID']."/",
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

}