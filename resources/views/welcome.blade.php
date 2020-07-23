<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <script   src="https://code.jquery.com/jquery-3.5.1.min.js"   integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="   crossorigin="anonymous"></script>

        <!-- Styles -->
        <link href="/style.css" rel="stylesheet">
    </head>
    <body>
    <div id="content">
    <h1 style="text-align: center">Season: {{$season}} - Week: <i id="currentWeek">1</i></h1>
        @if($fixtures->count() < 1)
            <div style="text-align: center;">
                <a id="createSeason" onclick="createSeason();" href="javascript:;">Please click here to create the season</a>
            </div>
        @else
<div id="autoPlayInner">

    <div style="width: 50%; float: left">

                    <table style="border:1px solid #000">
                    <thead>
                    <tr>
                        <th colspan="7" style="text-align: center">League Table</th>
                    </tr>
                    <tr>
                        <th>Teams</th>
                        <th>PTS</th>
                        <th>P</th>
                        <th>W</th>
                        <th>D</th>
                        <th>L</th>
                        <th>GD</th>
                    </tr>
                    </thead>
                        <tbody id="scoreTable">

                        </tbody>
                        <tfoot>
                            <tr >
                                <td colspan="6">
                                    <button onclick="playMatches()">Play All</button>
                                    <button onclick="autoPlay()">Play All Season Matches</button>
                                </td>
                                <td colspan="1" style="float: right; border: none"><button onclick="nextWeek()">Next Week</button></td>
                            </tr>

                        </tfoot>
                    </table>
    </div>
    <div style="float: left; width: 20%;">
        <table>
            <thead>
                <tr>
                    <th colspan="3" style="text-align: center">Match Results</th>
                </tr>
            </thead>
        <tbody id="matchResults"></tbody>
        </table>
    </div>

    <div style="width: 20%; float: left">
        <table >
            <div id="predictionList"></div>
        </table>
    </div>

</div>


            <div style="margin-top:10px; width: auto; clear: both" id="autoplayresults">

            </div>

            @endif
    </div>


    </body>

    <script>
        curSeason = {{$season}};
        curWeek = 1;

        $(document).ready(function(){

            getScoreTable(curSeason,curWeek);
            getFixtures(curSeason,curWeek);

        });

        function nextWeek(){

            if (curWeek !== {{$fixtures->count() > 0 ? $fixtures->max("week") : 1}}) {
                curWeek += 1;
                $("#currentWeek").html(curWeek);
            }
            if(curWeek >= 4){
                getPredictions()
            }

            getScoreTable(curSeason, curWeek);
            getFixtures(curSeason, curWeek);


        }

        function getPredictions() {
            $.ajax({
                method: 'get',
                url: "/api/getPredictions/"+curWeek,
                success: function (data) {
                    console.log(data);
                    var predictions = $("#predictionList");
                    predictions.html("");
                    $.each(data, function(i,e){
                        console.log(i,e)
                        predictions.append('<tr><td>'+i+'</td><td>%'+parseFloat(e).toFixed(2)+'</td></tr>')
                    });
                }
            })
        }

        function playMatches() {
            $.ajax({
                url: '/api/playMatches/'+curSeason+"/"+curWeek,
                method: 'get',
                success: function(){
                    getFixtures(curSeason, curWeek);
                    getScoreTable(curSeason, curWeek);
                    if(curWeek >= 4){
                        getPredictions()
                    }
                }
            })
        }

        function getScoreTable(season, week) {
            $.ajax({
                type: "get",
                url: "/api/scoreTable/"+season+"/"+week,
                success: function (data){
                    $("#scoreTable").html("");
                    $.each(data, function(i, e){
                        $("#scoreTable").append(' <tr>' +
                            '<td>'+e.name+'</td>' +
                            '<td>'+e.pts+'</td>' +
                            '<td>'+e.p+'</td>' +
                            '<td>'+e.w+'</td>' +
                            '<td>'+e.d+'</td>' +
                            '<td>'+e.l+'</td>' +
                            '<td>'+e.gd+'</td>' +
                            '</tr>');
                    })

                }
            })
        }

        function getFixtures(season, week) {
            $.ajax({
                type: "get",
                url: "/api/fixtures/"+season+"/"+week,
                success: function (data) {
                    $("#matchResults").html('');
                    $.each(data, function(i,e){
                        $("#matchResults").append('<tr>' +
                            '                    <td>'+e.home.name+'</td>' +
                            '                    <td>'+scoreShower(e["match"])+'</td>' +
                            '                    <td>'+e.away.name+'</td>' +
                            '                </tr>')
                    })
                }//not handling error
            });
        }

        function scoreShower(match){
            if(match === null)
                return "TBD"

            return match.homeScore + " - " + match.awayScore;

        }

        function createSeason() {
            $.ajax({
                url: '/api/createSeason/'+curSeason,
                method: 'get',
                success: function(){
                    location.reload();
                }
            })
        }

        function autoPlay() {
            $.ajax({
               url: '/api/playAll/'+curSeason,
               method: 'get',
               success: function (data) {

                    $.each(data, function(i,e){
                        $("#autoplayresults").append('<table style="width:auto">' +
                            '                    <thead>\n' +
                            '                    <tr>\n' +
                            '                        <th colspan="3" style="text-align: center">Match Results - Week '+i+++'</th>\n' +
                            '                    </tr>\n' +
                            '                    </thead>\n' +
                            '                    <tbody id="matchResults">');

                                $.each(e, function(innerIndex, innerVal){
                                    console.log(innerVal);
                                    $("#autoplayresults").append('<tr>' +
                                        '                    <td>'+innerVal.home.name+'</td>' +
                                        '                    <td>'+scoreShower(innerVal["match"])+'</td>' +
                                        '                    <td>'+innerVal.away.name+'</td>' +
                                        '                </tr>')
                                });
                            $("#autoplayresults").append('</tbody></table>');

                    });
                   getScoreTable(curSeason, curWeek);
                   getFixtures(curSeason, curWeek);
               }
            });
        }

    </script>
</html>
