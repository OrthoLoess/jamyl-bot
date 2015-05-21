<?php namespace JamylBot;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

	protected $fillable = ['name', 'owners'];

    public function users()
    {
        return $this->belongsToMany('JamylBot\User');
    }

    public function channels()
    {
        return $this->belongsToMany('JamylBot\Channel');
    }

    public function getOwners()
    {
        return explode(',', $this->owners);
    }

    /**
     * @param User $newOwner
     */
    public function addOwner($newOwner)
    {
        $owners = $this->getOwners();
        $owners[] = $newOwner->id;
        $this->owners = implode(array_unique($owners));
        $this->save();
    }

    public function isOwner($owner)
    {
        return in_array($owner, $this->getOwners());
    }

    public function isMemberBySlack($slack_id)
    {
        foreach ($this->users as $user){
            if ($user->slack_id == $slack_id) {
                return true;
            }
        }
        return false;
    }

}
