<?php
require_once "../db/SelectDB.php";

$db = new SelectDB();
//var_dump($_GET);
if ($_GET['method'] == 'table1') {
  echo json_encode($db->getGames($_GET));
} elseif($_GET['method'] == 'table2') {
  echo json_encode($db->getAllCountDownloadDevelopers($_GET));
} else {
  echo json_encode($db->getAllCountDownloadGenres($_GET));
}

