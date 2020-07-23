<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public function homeMatches(){
        return $this->hasMany(Match::class, 'home', 'id');
    }

    public function awayMatches(){
        return $this->hasMany(Match::class, 'away', 'id');
    }

}
