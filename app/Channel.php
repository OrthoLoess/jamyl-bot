<?php namespace JamylBot;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model {

	protected $fillable = ['slack_id', 'name'];

    public function groups()
    {
        $this->belongsToMany('JamylBot\Group');
    }

}
