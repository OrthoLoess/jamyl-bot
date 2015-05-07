<?php namespace JamylBot;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use JamylBot\Userbot\Userbot;

/**
 * Class User
 * @package JamylBot
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['char_name', 'email', 'password', 'slack_id',
        'slack_name', 'char_id', 'status', 'corp_id', 'corp_name', 'alliance_id', 'alliance_name'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    /**
     *
     */
    public function groups()
    {
        $this->belongsToMany('JamylBot\Group');
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'next_check'];
    }

    /**
     * @param $charInfo
     */
    public function updateAffiliation($charInfo)
    {
        $hasChanged = false;
        if ($this->corp_id != $charInfo['corporationID']) {
            $this->corp_id = $charInfo['corporationID'];
            $this->corp_name = $charInfo['corporationName'];
            $hasChanged = true;
        }
        if ($this->alliance_id != $charInfo['allianceID']) {
            $this->alliance_id = $charInfo['allianceID'];
            $this->alliance_name = $charInfo['allianceName'];
            $hasChanged = true;
        }
        if ($hasChanged) {
            $this->updateStatus();
        }
        $this->next_check = $charInfo['cachedUntil'];
        $this->save();
    }

    /**
     * @return bool
     */
    public function updateStatus()
    {
        if (in_array($this->alliance_id, config('standings.holders.alliances'))) {
            $this->status = 'holder';
            return true;
        }
        if (in_array($this->alliance_id, config('standings.blues.alliances')) || in_array($this->corp_id, config('standings.blues.corporations'))) {
            $this->status = 'blue';
            return true;
        }
        if (in_array($this->alliance_id, config('standings.light-blues.alliances')) || in_array($this->corp_id, config('standings.light-blues.corporations'))) {
            $this->status = 'light-blue';
            return true;
        }
        if (in_array($this->alliance_id, config('standings.reds.alliances')) || in_array($this->corp_id, config('standings.reds.corporations'))) {
            $this->status = 'red';
            return true;
        }
        $this->status = 'neutral';
        return true;
    }

    public function needsUpdate()
    {
        return $this->next_check->lte(Carbon::now());
    }

    public static function updateAll()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->updateStatus();
            $user->save();
        }
    }

    /**
     * @param $charId
     *
     * @return User
     */
    public static function findByChar($charId)
    {
        return User::where('char_id', $charId)->firstOrFail();
    }

    public static function findBySlack($slackId)
    {
        return User::where('slack_id', $slackId)->firstOrFail();
    }

    public static function listNeedUpdateIds($limit)
    {
        $allUsers = User::all();
        $users = [];
        foreach ($allUsers as $user) {
            if ($user->needsUpdate()) {
                $users[] = $user->char_id;
                if (count($users) >= $limit)
                    return $users;
            }
        }
        return $users;
    }

    public static function createAndFill($charArray)
    {
        $user = User::create($charArray);
        $userbot = new Userbot();
        $userbot->updateSingle($charArray['char_id']);
        return $user;
    }

}
