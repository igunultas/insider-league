<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get("/scoreTable/{season}/{week}", "ScoreTableController@getTable");

Route::get("/playMatches/{season}/{week}", "MatchController@play");

Route::post("/createFixture", "FixtureContorller@createFixtureForSeason");

Route::get("/fixtures/{season}/{week}", "FixtureController@getFixtures");

Route::get("/createSeason/{season}", "FixtureController@createFixtureForSeason");

Route::get("/getPredictions/{week}", "MatchController@calculatePercentage");

Route::get("/playAll/{season}", "MatchController@playAllMatches");
