<?php

use DiDom\ClassAttribute;
use DiDom\Document;
use DiDom\Element;
use DiDom\Encoder;
use DiDom\Errors;
use DiDom\Query;
use DiDom\StyleAttribute;
use DiDom\Exceptions\InvalidSelectorException;
require_once('DiDOM/src/DiDom/ClassAttribute.php');
require_once('DiDOM/src/DiDom/Document.php');
require_once('DiDOM/src/DiDom/Element.php');
require_once('DiDOM/src/DiDom/Encoder.php');
require_once('DiDOM/src/DiDom/Errors.php');
require_once('DiDOM/src/DiDom/Query.php');
require_once('DiDOM/src/DiDom/StyleAttribute.php');
require_once('DiDOM/src/DiDom/Exceptions/InvalidSelectorException.php');
require_once "db/InsertDB.php";
require_once "db/SelectDB.php";

class ParseAndSaveGame {

    private $selectDB;
    private $insertDB;
    private $redis;
    public function __construct()
    {
        $this->selectDB = new SelectDB();
        $this->insertDB = new InsertDB();
        $this->redis = new Predis\Client();
    }

    function parseGame($domGameDetail, $link) {
        $game = [];
        $game['name'] = trim($domGameDetail->find(".AHFaub span")[0]->text());
        $game['icon'] = $domGameDetail->find(".xSyT2c img::attr(src)")[0];
        $game['rating'] = '';
        if ($domGameDetail->find(".BHMmbe")[0]) {
            $game['rating'] = $domGameDetail->find(".BHMmbe")[0]->text();
        }

        $game['installs'] = '';
        foreach ($domGameDetail->find('.LXrl4c c-wiz[jsrenderer="HEOg8"] .hAyfc') as $td) {
            if ($td->find(".BgcNfc")[0]->text() == "Installs") {
                $game['installs'] = $td->find("span div span")[0]->text();
                break;
            }
        }
        $game['installs'] = preg_replace("/[^0-9]/", '', $game['installs']);
//    $installsGame = $domGameDetail->find('.LXrl4c c-wiz[jsrenderer="HEOg8"] .hAyfc:eq(2) .IQ1z0d span')[0]->text();


        $game['developer_address'] = '';
        foreach ($domGameDetail->find('.LXrl4c c-wiz[jsrenderer="HEOg8"] .hAyfc') as $td) {
            if ($td->find(".BgcNfc")[0]->text() == "Developer") {
                foreach ($td->find("span div span div") as $itm) {
                    if (!$itm->has("a")) {
                        $game['developer_address'] = str_replace(array("r","n"),"",trim($td->find("span div span")[0]->lastChild()->text()));
                        break;
                    }
                }
                break;
            }
        }

        $game['developer_name'] = str_replace(array("r","n"),"",trim($domGameDetail->find(".ZVWMWc .qQKdcc")[0]->firstchild()->text()));
        $game['category'] = $domGameDetail->find(".ZVWMWc .qQKdcc")[0]->lastchild()->text();
        $game['link_game'] = $link;
//        foreach ($domGameDetail->find(".T32cc") as $keyCat => $pqGameCat) {
//            $game['category'][$keyCat] = $pqGameCat->find("a")[0]->text();
//        }

       return $this->saveGame($game);
    }

    function saveGame($game = []) {
        $keyDevelopers = 'developers';
        $keyGenres = 'genres';

        $developerRedis = $this->redis ->hget($keyDevelopers, $game['developer_name']);
        $developerId = '';
        if (!$developerRedis) {
            $developer =  $this->insertDB->addDeveloper($game['developer_name'], $game['developer_address']);
            if ($developer) {
//                $developerId = $developer;
                $this->redis->hset($keyDevelopers, $game['developer_name'], $developer);
            }
        } else {
            $developerId = $this->redis->hget($keyDevelopers, $game['developer_name']);
        }
        $genreRedis = $this->redis->hget($keyGenres, $game['category']);
        $genreId = '';
        if (!$genreRedis) {
            $genreRu = $this->selectDB->getGenreRu($game['category']);
            $genre = $this->insertDB->addGenre($genreRu);
            if ($genre) {
//                $genreId = $genre;
                $this->redis->hset($keyGenres, $game['category'], $genre);
            }
        } else {
            $genreId = $this->redis->hget($keyGenres, $game['category']);
        }
        $resGame = $this->insertDB->addGame($game);
//        $gameId = '';
        if ($resGame) {
//            $gameId = $resGame;
            $this->insertDB->game_developer_genre($resGame, $developerId, $genreId);
        }

//        $this->query("INSERT INTO developer_game VALUES(null,".$developerId.",".$gameId.")");
//        $this->query("INSERT INTO genre_game VALUES(null,".$genreId.",".$gameId.")");


//        echo "\nНазвание: ".$game['name'] ;
//        echo "\nКатегория: \n";
//
//        echo "\n";
//        echo "Ссылка на иконку: ".$game['icon']."\n";
//        echo "Рейтинг: ".$game['rating']."\n";
//        echo "Кол-во установок: ".$game['installs'] ."\n";
//        echo "Разработчик: ".$game['developer']."\n";

        return $resGame;
    }
}