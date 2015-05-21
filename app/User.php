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
        return $this->belongsToMany('JamylBot\Group');
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'next_check'];
    }

    /**
     * @param $charInfo
     */
    public function updateAffiliation($charInfo)
    {
        if ($this->corp_id != $charInfo['corporationID']) {
            $this->corp_id = $charInfo['corporationID'];
            $this->corp_name = $charInfo['corporationName'];
        }
        if ($this->alliance_id != $charInfo['allianceID']) {
            $this->alliance_id = $charInfo['allianceID'];
            $this->alliance_name = $charInfo['allianceName'];
        }
        $this->updateStatus();
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

    /**
     * @return bool
     */
    public function needsUpdate()
    {
        return $this->next_check === null || $this->next_check->lte(Carbon::now());
    }

    /**
     * @return bool
     */
    public function hasAccess()
    {
        if ($this->status == 'holder' || $this->status == 'blue') {
            return true;
        }
        return false;
    }

    /**
     *
     */
    public static function updateAll()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->updateStatus();
            $user->save();
        }
    }

    /**
     * @param int $size
     *
     * @return string
     * @throws \Exception
     */
    public function getAvatarUrl($size = 128)
    {
        if (!in_array($size, config('eve.avatar_sizes'))){
            throw new \Exception('Invalid avatar size requested.');
        }
        return config('eve.avatar_url').$this->char_id.'_'.$size.'.jpg';
    }

    /**
     * @param array $slackUsers
     */
    public function searchSlackList($slackUsers)
    {
        foreach ($slackUsers as $slackUser) {
            if (!$slackUser['deleted'] && !$slackUser['is_bot'] && !$slackUser['deleted'] && $slackUser['profile']['email'] == $this->email) {
                $this->slack_id = $slackUser['id'];
                $this->slack_name = $slackUser['name'];
                $this->save();
            }
        }
    }

    /**
     * @param int $charId
     *
     * @return User
     */
    public static function findByChar($charId)
    {
        return User::where('char_id', $charId)->firstOrFail();
    }

    /**
     * @param string $slackId
     *
     * @return User
     */
    public static function findBySlack($slackId)
    {
        return User::where('slack_id', $slackId)->firstOrFail();
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public static function findByEmail($email)
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * @param int $limit
     *
     * @return array
     */
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

    /**
     * @param array $charArray
     *
     * @return User
     */
    public static function createAndFill($charArray)
    {
        $user = User::create($charArray);
        $userbot = new Userbot();
        $userbot->updateSingle($charArray['char_id']);
        return $user;
    }

}
