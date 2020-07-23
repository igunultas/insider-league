<?php

namespace App\Http\Controllers;

use App\DB\Team;
use App\DB\Fixture;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function createFixtureForSeason()
    {
        $teamList = Team::all();
        //setting season
        $season = Fixture::max('season');
        $season += 1;

        $teamList = $teamList->shuffle();

        $teams = $teamList->map(function($i){
            return $i->id;
        })->toArray();

        $away = array_splice($teams,(count($teams)/2));
        $home = $teams;
        for ($i=0; $i < count($home)+count($away)-1; $i++){
            for ($j=0; $j<count($home); $j++){
                $round[$i][$j]["homeTeam"]=$home[$j];
                $round[$i][$j]["awayTeam"]=$away[$j];
            }
            if(count($home)+count($away)-1 > 2){
                $array = array_splice($home, 1, 1);
                array_unshift($away,array_shift($array));
                array_push($home,array_pop($away));
            }
        }

        $latest = $round;
        //creating second league
        foreach($round as $r){
            $new = [];
            foreach($r as $e){
                array_push($new, ["homeTeam" => $e["awayTeam"], "awayTeam" => $e["homeTeam"]]);
            }
            array_push($latest, $new);
        }

        foreach($latest as $week => $item){

            foreach($item as $inner){

                $fixture = new Fixture;
                $fixture->homeTeam = $inner["homeTeam"];
                $fixture->awayTeam = $inner["awayTeam"];
                $fixture->week = $week+1;
                $fixture->season = $season;
                $fixture->status = 0;
                $fixture->save();
            }

        }


        return ["status" => 1];
    }

    public function getFixtures($season, $week){
        return Fixture::with(["home", "away", "match"])->where("week", $week)->where("season", $season)->get();
    }

}
