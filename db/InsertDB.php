<?php
require_once "Connection.php";

class InsertDB extends Connection
{
    public static $con = '';
    function addDeveloper($developer_name, $developer_address)
    {
        $developer = $this->query("INSERT INTO developers VALUES(null, '".$developer_name."', '".$developer_address."')");
        return $developer;
    }
    function addGenre($genreRu)
    {
        $genre = $this->query("INSERT INTO genres VALUES(null, '".$genreRu."')");
        return $genre;
    }
    function addGame($game)
    {
        $resGame = $this->query("INSERT INTO games VALUES(null, '".$game['name']."', '".$game['icon']."', '".$game['rating']."', '".$game['installs']."', '".$game['link_game']."')");
        return $resGame;
    }
    function game_developer_genre($resGame, $developerId, $genreId) {
        $this->query("INSERT INTO game_developer_genre VALUES(null,".$resGame.",".$developerId.",".$genreId.")");
    }
}