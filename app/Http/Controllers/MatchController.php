<?php

namespace App\Http\Controllers;

use App\DB\Fixture;
use App\DB\Match;
use App\DB\Team;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public $homeWonConst = 0.05;
    public $awayWonConst = 0.10;
    public $homeLostConst = 0.15;
    public $awayLostConst = 0.05;
    public $maxGoals = 5;

    public function play($season, $week){

        $fixture = Fixture::where("season", $season)->where("week", $week)->where("status", 0)->get();

        foreach($fixture as $fix){
            $this->playSingleMatch($fix->homeTeam, $fix->awayTeam, $fix->week, $fix->id);
            $fix->status = 1;
            $fix->save();
        }

        return ["status" => 1];

    }

    public function playAllMatches(int $season, int $week = 0){
        $teams = Team::count();
        $totalWeek = ($teams-1)*2;

        for($i = 1; $i<=$totalWeek; $i++){
            $this->play($season, $i);
        }

//        if($week === 0){
//
//            for($currentWeek = 0; $currentWeek < $totalWeek; $$currentWeek++){
//                $this->play($season, $currentWeek);
//            }
//            return Match::where('season', $season)->get();
//        }else{
//            $this->play($season, $week);
//        }

         $fixtures = Fixture::with(['away','home','match'])->where('season', $season)->get();

        return $fixtures->groupBy("week");
    }


    protected function playSingleMatch($homeTeam, $awayTeam, $week, $fixture_id){

        $homeTeamStr = $this->calculateStr($homeTeam, 1);
        $awayTeamStr = $this->calculateStr($awayTeam, 2);

        $percentages =[$homeTeamStr, $awayTeamStr, ($homeTeamStr+$awayTeamStr/2)];

        do {
            $winner = $this->selectWinner($percentages);
        } while ($winner == null);


        $score = $this->calculateScore(($winner === 2) ? 0 : 1);

        if($winner == 0){
            $homeScore = $score[0];
            $awayScore  = $score[1];
        }else if($winner == 1){
            $homeScore = $score[1];
            $awayScore = $score[0];
        }else{
            $homeScore = $score[0];
            $awayScore = $score[0];
        }

        $match = new Match;
        $match->home = $homeTeam;
        $match->away = $awayTeam;
        $match->homeScore = $homeScore;
        $match->awayScore = $awayScore;
        $match->week = $week;
        $match->fixture_id = $fixture_id;
        $match->save();

        return $match;
    }

    //Type must be 1 or 2 determines that team is playing in home or not
    //there might be optimization about calculating str of the team with saving the results for next calculation and also effecting the team that playing against.

    protected function calculateStr(int $team, int $type, $teams = null, $week = null){

        if($teams != null){
            $team = $teams->where("id", $team)->first();
        }else{
            $team = Team::find($team);
        }

        $previousMatches = Match::where(function($query) use($team){
            $query->where('home', $team->id)->orWhere('away', $team->id);
        });
        if($week != null){
            $previousMatches = $previousMatches->where("week", "<=", $week);
        }


        $previousMatches = $previousMatches->get();


        if($type == 1)
            $baseStr = $team->home_str;
        else
            $baseStr = $team->away_str;

        foreach($previousMatches as $match){

            $matchStatus = $this->isWon($match, $team->id);
            if($matchStatus == 1){
               $baseStr += $baseStr*$this->homeWonConst;
            }else if($matchStatus == 3){
                $baseStr += $baseStr*$this->awayWonConst;
            }else if($matchStatus == -1){
                $baseStr -= $baseStr*$this->homeLostConst;
            }else if($matchStatus == -2){
                $baseStr -= $baseStr*$this->awayLostConst;
            }
        }


        return $baseStr;
    }


    public function calculatePercentage($week){

        $teamList = Team::all();
        $strList = [];
        foreach($teamList as $team){
            $home = $this->calculateStr($team->id, 1, $teamList, $week);
            $away = $this->calculateStr($team->id, 2, $teamList, $week);
            $total = ($home+$away)/2;
            $strList[$team->name] = $total;
        }

        $totalNo =  array_sum($strList);
        $percentages = [];
        foreach($strList as $key => $str){
            $percentages[$key] = ($str/$totalNo*100);
        }

        arsort($percentages);
        return $percentages;
    }

    //returns if the match is won or not
    //1 for match is won at home
    //0 for matchh is lost at home
    //-1 for match is draw at home
    //3 for match is won at away
    //2 for match is lost at away;
    //-2 for match is draw at away

    public function isWon(Match $match, int $team){
        if($match->home === $team){
            if(($match->homeScore-$match->awayScore) > 0){
                return 1;
            }else if(($match->homeScore-$match->awayScore) < 0){
                return 0;
            }else {
                //draw
                return -1;
            }
        }else{
            if(($match->awayScore-$match->homeScore) > 0){
                return 3;
            }else if(($match->awayScore-$match->homeScore) < 0){
                return 2;
            }else {
                //draw
                return -2;
            }
        }

    }

    protected function selectWinner(array $percentages){
        $totalProbability = 0;

        foreach ($percentages as $item => $probability) {
            $totalProbability += $probability;
        }

        $stopAt = rand(0, $totalProbability);
        $currentProbability = 0;

        foreach ($percentages as $item => $probability) {
            $currentProbability += $probability;
            if ($currentProbability >= $stopAt) {
                return $item;

            }
        }
        return null;
    }

    //type is win lose or draw

    //1 is win lose  draw is -1

    protected function calculateScore($type){
        if($type === 1){
            $winner = rand(1,$this->maxGoals);
            $loser = rand(0, $winner);
            return [$winner, $loser];
        }

        $draw = rand(0,$this->maxGoals);
        return [$draw, $draw];
    }



}
