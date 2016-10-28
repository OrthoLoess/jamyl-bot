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

    public function setOwners($ownersArray)
    {
        $this->owners = implode(',', $ownersArray);
        $this->save();
    }

    /**
     * @param int $newOwner
     */
    public function addOwner($newOwner)
    {
        $owners = $this->getOwners();
        $owners[] = $newOwner;
        $this->owners = implode(',', array_unique($owners));
        $this->save();
    }

    public function removeOwner($owner)
    {
        $owners = $this->getOwners();
        $owners = array_diff($owners, [$owner]);
        $this->setOwners($owners);
    }

    public function isOwner($owner)
    {
        return in_array($owner, $this->getOwners());
    }

    public function isMemberBySlack($slack_id)
    {
        foreach ($this->users as $user){
            /** @var User $user */
            if ($user->slack_id != null && $user->slack_id == $slack_id && $user->hasAccess()) {
                return true;
            }
        }
        return false;
    }

    public function getCorps()
    {
        return array_filter(explode(',', $this->corps));
    }

    public function setCorps($newCorps)
    {
        $this->corps = implode(',', $newCorps);
        $this->save();
    }

    public function addCorp($newCorp)
    {
        $corps = $this->getCorps();
        $corps[] = $newCorp;
        $this->corps = implode(',', array_unique($corps));
        $this->save();
    }

    public function removeCorp($corp)
    {
        $corps = $this->getCorps();
        $corps = array_diff($corps, [$corp]);
        $this->setCorps($corps);
    }

    public function hasCorp($corp)
    {
        return in_array($corp, $this->getCorps());
    }

}
