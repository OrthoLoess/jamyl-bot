<?php namespace JamylBot;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

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
        if ($this->corpId != $charInfo['corporationID']) {
            $this->corpId = $charInfo['corporationID'];
            $this->corpName = $charInfo['corporationName'];
            $hasChanged = true;
        }
        if ($this->allianceId != $charInfo['allianceID']) {
            $this->allianceId = $charInfo['allianceID'];
            $this->allianceName = $charInfo['allianceName'];
            $hasChanged = true;
        }
        if ($hasChanged) {
            $this->updateStatus();
        }
        $this->nextCheck = $charInfo['cachedUntil'];
        $this->save();
    }

    /**
     * @return bool
     */
    public function updateStatus()
    {
        if (in_array($this->allianceId, config('standings.holders.alliances'))) {
            $this->status = 'holder';
            return true;
        }
        if (in_array($this->allianceId, config('standings.blues.alliances')) || in_array($this->corpId, config('standings.blues.corporations'))) {
            $this->status = 'blue';
            return true;
        }
        if (in_array($this->allianceId, config('standings.light-blues.alliances')) || in_array($this->corpId, config('standings.light-blues.corporations'))) {
            $this->status = 'light-blue';
            return true;
        }
        if (in_array($this->allianceId, config('standings.reds.alliances')) || in_array($this->corpId, config('standings.reds.corporations'))) {
            $this->status = 'red';
            return true;
        }
        $this->status = 'neutral';
        return true;
    }

    public function needsUpdate()
    {
        return $this->nextCheck->lte(Carbon::now());
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
                $users[] = $user->charId;
                if (count($users) >= $limit)
                    return $users;
            }
        }
        return $users;
    }

}
