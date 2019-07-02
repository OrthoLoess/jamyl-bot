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
class Pinger extends Model {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'pingers';


    protected $primaryKey = 'slack_id';
    public $timestamps = false;
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['slack_id', 'display_name'];

    /**
     * @param string $slackId
     *
     * @return Bool
     */
    public static function getDisplayName($slackId)
    {
        $first = Pinger::where('slack_id', $slackId)->first();
        if(!$first) {
            return null;
        }else {
            return $first->display_name;
        }
    }

}
