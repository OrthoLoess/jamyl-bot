<?php namespace JamylBot;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

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

    public function groups()
    {
        $this->belongsToMany('JamylBot\Group');
    }

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
    }

    public function updateStatus()
    {
        // decide if red/blue/etc
    }

//    public function channels()
//    {
//        $this->hasManyThrough('JamylBot\Channel', 'JamylBot\Group');
//    }

}
