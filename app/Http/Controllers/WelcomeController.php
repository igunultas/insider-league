<?php

namespace App\Http\Controllers;

use App\DB\Fixture;
use App\DB\Team;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request){
        $teams = Team::with('homeMatches', 'awayMatches')->get();
        if($request->has("season"))
            $season = $request->get('season');
        else
            $season = 1;

        $fixtures = Fixture::with(["home", "away", "match"])->where("season", $season)->get();

        return view("welcome", ["teams" => $teams, "fixtures" => $fixtures, "season" => $season]);
    }
}
//3 points for win
//1 points for draw

//team str calculation
