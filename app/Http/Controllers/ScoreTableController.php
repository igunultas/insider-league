<?php

namespace App\Http\Controllers;

use App\DB\Fixture;
use App\DB\Team;
use Illuminate\Http\Request;

class ScoreTableController extends Controller
{
    protected $win = 3;
    protected $draw = 1;

    public function getTable($season, $week){
        $fixtures = Fixture::with("match")->where("season", $season)->where("week", '<=', $week)->get();
        $teams = Team::all();

        $teamWithPts = [];

        foreach($teams as $team){
            $teamWithPts[$team->id] = ["name" => $team->name, "pts" => 0, "p" => 0, "w" => 0, "d" => 0, "l" => 0, "gd" => 0, "goalsIn" => 0, "goalsOut" => 0];
        }


        foreach($fixtures as $fixture){
            if($fixture->match == null) continue;

            $home = $teamWithPts[$fixture->homeTeam];
            $away = $teamWithPts[$fixture->awayTeam];
            if($fixture->match->homeScore > $fixture->match->awayScore){
               $home["pts"] += $this->win;
               $home["p"] += 1;
               $home["w"]+=1;
               $home["goalsIn"] += $fixture->match->homeScore;
               $home["goalsOut"] += $fixture->match->awayScore;

               $away["p"] += 1;
               $away["l"]+=1;
               $away["goalsIn"] += $fixture->match->awayScore;
               $away["goalsOut"] += $fixture->match->homeScore;

            }else if($fixture->match->homeScore < $fixture->match->awayScore){
                $away["pts"] += 3;
                $away["p"] += 1;
                $away["w"]+=1;
                $away["goalsIn"] += $fixture->match->awayScore;
                $away["goalsOut"] = $fixture->match->homeScore;
                $home["p"] += 1;
                $home["l"]+=1;
                $home["goalsIn"] += $fixture->match->homeScore;
                $home["goalsOut"] += $fixture->match->awayScore;
            }else{
                $home["goalsIn"] += $fixture->match->homeScore;
                $home["goalsOut"] += $fixture->match->awayScore;
                $home["pts"] += $this->draw;

                $home["p"] += 1;
                $home["d"] += 1;
                $away["goalsIn"] += $fixture->match->awayScore;
                $away["goalsOut"] += $fixture->match->homeScore;
                $away["p"] += 1;
                $away["pts"] += $this->draw;
                $away["d"] +=1;
            }
            $home["gd"] += $fixture->match->homeScore-($fixture->match->awayScore*2);
            $away["gd"] += ($fixture->match->awayScore*2)-$fixture->match->homeScore;

            $teamWithPts[$fixture->homeTeam] = $home;
            $teamWithPts[$fixture->awayTeam] = $away;

        }

        $teams = collect($teamWithPts);
        $teams = $teams->sortByDesc(function($item){
            return $item["pts"];
        });

        return array_values($teams->toArray());
    }
}
