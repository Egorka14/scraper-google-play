<?php

require "predis/autoload.php";
Predis\Autoloader::register();

use DiDom\Document;
require_once('DiDOM/src/DiDom/ClassAttribute.php');
require_once('DiDOM/src/DiDom/Document.php');
require_once('DiDOM/src/DiDom/Element.php');
require_once('DiDOM/src/DiDom/Encoder.php');
require_once('DiDOM/src/DiDom/Errors.php');
require_once('DiDOM/src/DiDom/Query.php');
require_once('DiDOM/src/DiDom/StyleAttribute.php');
require_once('DiDOM/src/DiDom/Exceptions/InvalidSelectorException.php');
require_once("PredisCustom.php");
require_once('ParseAndSaveGame.php');

$category = 'GAME';
$country = "ru";
$url = 'https://play.google.com/store/apps/top/category/'.$category."?gl=".$country;
$keyAppsListCount = 'apps_list_count';
$keyAppsList = 'apps_list';
$start = microtime(true);
$redis = new PredisCustom();

$games = [];
$apps = [];
$countAppsRedis = 0;

//$redis->delAll(); // del All keys
//exit();

if (!$redis->ex($keyAppsListCount)) {
    $redis->createCounter($keyAppsListCount);
    $redis->setExp($keyAppsListCount, 3600 * 24);
} else {
    $countAppsRedis = (int)$redis->get($keyAppsListCount, ['method' => '']);
}

if ($redis->ex($keyAppsList)) {
    $apps = $redis->get($keyAppsList, ['method' => 'array', 'function' => 'smembers']);
}

$dom = new Document($url, true);
foreach($dom->find(".UBeTzd") as $key => $pq) {
    $linkCategory = "https://play.google.com" . $pq->find('.id-track-click::attr(href)')[0];
    $domCategory = new Document($linkCategory, true);
    foreach ($domCategory->find(".ImZGtf") as $k => $pqGame) {
//        echo "\nLink: " . $pqGame->find(".b8cIId a::attr(href)")[0] . "\n";
        $linkGame = "https://play.google.com" . $pqGame->find(".b8cIId a::attr(href)")[0];
        if (!in_array($linkGame, $apps)) {
            $apps[] = $linkGame;
            $redis->set($keyAppsList,['method' => 'array', 'value' => $linkGame]);
        }
    }
}

$redis->setExp($keyAppsList, 3600 * 24);
for ($i = $countAppsRedis; $i < count($apps); ) {
    if ($i == 5000) {
        break;
    }
    $app = $apps[$i]."&gl=".$country;
    echo "\nApp: ".$app;

    try {
        $domGameDetail = new Document($app, true);
    } catch (Exception $exception) {
        $domGameDetail = null;
    }

    if ($domGameDetail) {
        if ($domGameDetail !== FALSE) {
            $ts = $domGameDetail->find('.tlG8q .W9yFB');
            $parseAndSaveGame = new ParseAndSaveGame();
            echo "\nЗапись game: ".$parseAndSaveGame->parseGame($domGameDetail, $app);
            for ($k = 0; $k < count($ts); ) {
                $pqGame = $ts[$k];
                $linkCollection = "https://play.google.com" . $pqGame->find("a::attr(href)")[0];
                echo "\nLink collection: ".$linkCollection;

                try {
                    $domCollection = new Document($linkCollection, true);
                } catch (Exception $exception) {
                    $domCollection = null;
                }
                if ($domCollection) {
                    if ($domCollection !== FALSE) {
                        foreach ($domCollection->find(".ImZGtf") as $z => $pqCollection) {
                            $linkGame = "https://play.google.com" . $pqCollection->find(".b8cIId a::attr(href)")[0];

                            if (!in_array($linkGame, $apps)) {
                                $apps[] = $linkGame;
                                $redis->set($keyAppsList, ['method' => 'array', 'value' => $linkGame]);
                            }
                            echo "\n Link: " . $linkGame;
                            echo "\nЗаписан: " . $redis->get($keyAppsList, ['method' => 'array', 'function' => 'sismember', 'value' => $linkGame, 'start' => 0, 'finish' => -1]);
                            echo "\nz: " . $z;
                            echo "\nApps: " . count($apps);
                        }
                        $k++;
                    } else {
                        echo "\nЗадержка для повтора подключения на 25 сек;";
                        sleep(25);
                    }
                } else {
                    echo "\n Коллекция не найдена";
                    $k++;
                }
            }

            $i++;
            $redis->inc($keyAppsListCount);
        } else {
            echo "\nЗадержка для повтора подключения на 25 сек;";
            sleep(25);
        }
        echo "\ni: ".$i;
        echo "\nApps: " . count($apps);
    } else {
        echo "\n Приложение не найдено";
        $i++;
    }

}

//$redis->delAll();

$finish = microtime(true);
$delta = ($finish - $start) / 60;
echo "\nЗагрузка окончена";
echo "\nВремя выполнения: ". $delta . ' мин.';
echo "\nКоличество Apps: ".count($apps)."\n";