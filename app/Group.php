<?php namespace JamylBot;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

	protected $fillable = ['name'];

    public function users()
    {
        $this->belongsToMany('JamylBot\User');
    }

    public function channels()
    {
        $this->belongsToMany('JamylBot\Channel');
    }

    public function getOwners()
    {
        return explode(',', $this->owners);
    }

    public function addOwner($newOwner)
    {
        $owners = $this->getOwners();
        $owners[] = $newOwner;
        $this->owners = implode(array_unique($owners));
        $this->save();
    }

    public function isOwner($owner)
    {
        return in_array($owner, $this->getOwners());
    }

}
