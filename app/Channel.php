<?php namespace JamylBot;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model {

	protected $fillable = ['slack_id', 'name', 'is_group'];

    public function groups()
    {
        return $this->belongsToMany('JamylBot\Group');
    }

}
