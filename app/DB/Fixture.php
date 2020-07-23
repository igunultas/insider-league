<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    //
    public function home(){
        return $this->hasOne(Team::class, 'id', 'homeTeam');
    }

    public function away(){
        return $this->hasOne(Team::class, 'id', 'awayTeam');
    }

    public function match(){
        return $this->hasOne(Match::class, 'fixture_id', 'id');
    }

}
