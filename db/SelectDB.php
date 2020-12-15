<?php
require_once "Connection.php";

class SelectDB extends Connection
{

  function getCountApps()
  {
    $countApps = $this->con()->query("SELECT count(*) as count FROM games");
    return $countApps->fetch_row()[0];
  }

  function getCountDevelopers()
  {
    $countDevelopers = $this->con()->query("SELECT count(*) as count FROM developers");
    return $countDevelopers->fetch_row()[0];
  }

  function getCountGenres()
  {
    $countGenres = $this->con()->query("SELECT count(*) as count FROM genres");
    return $countGenres->fetch_row()[0];
  }

  function getCountGames()
  {
    $countApps = $this->con()->query("SELECT count(*) as count FROM games");
    return $countApps->fetch_row()[0];
  }

  function getGenreRu($genreUS) {
    switch ($genreUS) {
      case 'Action':
        $genre = 'Экшен';
        break;
      case 'Casual':
        $genre = 'Казуальные';
        break;
      case 'Arcade':
        $genre = 'Аркады';
        break;
      case 'Simulation':
        $genre = 'Симуляторы';
        break;
      case 'Puzzle':
        $genre = 'Головоломки';
        break;
      case 'Board':
        $genre = 'Настольные';
        break;
      case 'Word':
        $genre = 'Словесные игры';
        break;
      case 'Brain Games':
        $genre = 'Мозговые Игры';
        break;
      case 'Trivia':
        $genre = 'Викторины';
        break;
      case 'Role Playing':
        $genre = 'Ролевые';
        break;
      case 'Racing':
        $genre = 'Гонки';
        break;
      case 'Action & Adventure':
        $genre = 'Приключенческий боевик';
        break;
      case 'Adventure':
        $genre = 'Приключения';
        break;
      case 'Education':
        $genre = 'Образование';
        break;
      case 'Card':
        $genre = 'Карточные';
        break;
      case 'Strategy':
        $genre = 'Стратегии';
        break;
      case 'Sports':
        $genre = 'Спортивные игры';
        break;
      case 'Music':
        $genre = 'Музыка';
        break;
      case 'Entertainment':
        $genre = 'Развлечения';
        break;
      case 'Educational':
        $genre = 'Образовательные';
        break;
      case 'Lifestyle':
        $genre = 'Стиль жизни';
        break;
      case 'Communication':
        $genre = 'Связь';
        break;
      case 'Social':
        $genre = 'Социальные';
        break;
      case 'Video Players & Editors':
        $genre = 'Видеоплееры и редакторы';
        break;
      case 'Productivity':
        $genre = 'Работа';
        break;
      case 'Shopping':
        $genre = 'Покупки';
        break;
      case 'Maps & Navigation':
        $genre = 'Карты и навигация';
        break;
      case 'Personalization':
        $genre = 'Персонализация';
        break;
      case 'Music & Audio':
        $genre = 'Музыка и аудио';
        break;
      case 'Tools':
        $genre = 'Инструменты';
        break;
      case 'Pretend Play':
        $genre = 'От 6 до 12 лет';
        break;
      case 'Casino':
        $genre = 'Казино';
        break;
      case 'Books & Reference':
        $genre = 'Книги и справочники';
        break;
      case 'Auto & Vehicles':
        $genre = 'Автомобили и транспорт';
        break;
      case 'Health & Fitness':
        $genre = 'Здоровье и фитнес';
        break;
      case 'DM Studio':
        $genre = 'DM Studio';
        break;
      case 'Creativity':
        $genre = 'Креативные';
        break;
      case 'Art & Design':
        $genre = 'Искусство и дизайн';
        break;
      case 'House & Home':
        $genre = 'Жилье и дом';
        break;
      case 'Photography':
        $genre = 'Фотография';
        break;
      case 'Travel & Local':
        $genre = 'Путешествия';
        break;
      case 'Business':
        $genre = 'Бизнес';
        break;
      case 'Comics':
        $genre = 'Комиксы';
        break;
      case 'Music & Video':
        $genre = 'Music & Video';
        break;
      case 'Weather':
        $genre = 'Погода';
        break;
      case 'Food & Drink':
        $genre = 'Еда и напитки';
        break;
      default:
        $genre = $genreUS;
    }
    return $genre;
  }


  function getGames($get = [])
  {
    $draw = $get['draw'];
    $start = $get['start'];
    $length = $get['length'];
    $order = $get['order'];
    $searchValue = $get['search']['value'];
    ## Custom Field value
    $searchCustomFilter = $get['searchByFilter'];
    $searchCustomValue = $get['searchByValue'];

    if (isset($order)) {
      $columnSortable = 'id';
      switch ($order[0]['column'] + 1) {
        case 2:
          $columnSortable = 'name';
          break;
        case 3:
          $columnSortable = 'rating';
          break;
        case 4:
          $columnSortable = 'installs';
          break;
        case 5:
          $columnSortable = 'developer_name';
          break;
        case 6:
          $columnSortable = 'developer_address';
          break;
        case 7:
          $columnSortable = 'genre';
          break;
        default:
          $columnSortable = 'id';
          break;
      }

      $sort = $order[0]['dir'];
    } else {
      $columnSortable = 'id';
      $sort = 'desc';
    }

    ## Search
    $searchQuery = " ";
    if ($searchCustomFilter != '' && $searchCustomValue != '') {
      switch ($searchCustomFilter) {
        case "developer_name":
          $searchQuery .= " AND (ds.name LIKE '%" . $searchCustomValue . "%' ) ";
          break;
        case "developer_address":
          $searchQuery .= " AND (ds.address LIKE '%" . $searchCustomValue . "%' ) ";
          break;
        case "genre":
          $searchQuery .= " AND (gs.name LIKE '%" . $searchCustomValue . "%' ) ";
          break;
        default:
          break;
      }
    }
    if ($searchValue != '') {
      $searchQuery .= " and (g.name like '%" . $searchValue . "%' ) ";
    }

    ## Total number of records without filtering
    $sel = $this->con()->query("select count(*) as allcount from games g");
    $records = $sel->fetch_assoc();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sel = $this->con()->query("select count(*) as allcount FROM games g, game_developer_genre gdg, genres gs, developers as ds WHERE g.id = gdg.game AND gdg.genre = gs.id AND gdg.developer = ds.id $searchQuery");
    $records = $sel->fetch_assoc();
    $totalRecordWithFilter = $records['allcount'];
    $result = $this->con()->query("SELECT g.id, g.name, g.icon, g.rating, g.installs, gs.name as genre, ds.name as developer_name, ds.address as developer_address, g.link  FROM games g, game_developer_genre gdg, genres gs, developers as ds WHERE g.id = gdg.game AND gdg.genre = gs.id AND gdg.developer = ds.id $searchQuery  ORDER BY $columnSortable $sort LIMIT $start, $length");
    $games = [];
    while ($game = $result->fetch_assoc()) {
      $genre = $this->getGenreRu($game['genre']);

      $games[] = [
        'id' => $game['id'],
        'name' => "<a href='" . $game['icon'] . "' target='_blank'><img src='" . $game['icon'] . "' width='25' height='25' /></a><a href='" . $game['link'] . "' target='_blank'>" . $game['name'] . "</a>",
        'rating' => $game['rating'],
        'installs' => number_format($game['installs']),
        'developer_name' => $game['developer_name'],
        'developer_address' => $game['developer_address'],
        'genre' => $genre,

      ];
    }
    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordWithFilter,
      "aaData" => $games
    );
    return $response;
  }

  function getAllCountDownloadDevelopers($get = [])
  {
    $draw = $get['draw'];
    $start = $get['start'];
    $length = $get['length'];
    $order = $get['order'];
    $searchValue = $get['search']['value'];

    if (isset($order)) {
      switch ($order[0]['column'] + 1) {
        case 2:
          $columnSortable = 'sum_download';
          break;
        default:
          $columnSortable = 'developer_name';
          break;
      }

      $sort = $order[0]['dir'];
    } else {
      $columnSortable = 'developer_name';
      $sort = 'asc';
    }

    $searchQuery = " ";
    if ($searchValue != '') {
      $searchQuery .= " and (ds.name like '%" . $searchValue . "%' ) ";
    }

    ## Total number of records without filtering
    $sel = $this->con()->query("select count(DISTINCT ds.name) as allcount FROM developers ds");
    $records = $sel->fetch_assoc();
    $totalRecords = $records['allcount'];

    $sel = $this->con()->query("select count(DISTINCT ds.name) as allcount FROM developers ds WHERE 1 $searchQuery");
    $records = $sel->fetch_assoc();
    $totalRecordWithFilter = $records['allcount'];
//    var_dump($totalRecordWithFilter);

    $result = $this->con()->query("SELECT ds.name as developer_name, SUM(g.installs) as sum_download  FROM developers ds, games g, game_developer_genre gdg WHERE g.id = gdg.game AND gdg.developer = ds.id $searchQuery GROUP BY ds.name ORDER BY $columnSortable $sort LIMIT $start, $length");
    $games = [];
    while ($game = $result->fetch_assoc()) {
      $games[] = [
        'developer_name' => $game['developer_name'],
        'sum_download' => number_format($game['sum_download']),

      ];
    }
    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordWithFilter,
      "aaData" => $games
    );
    return $response;
  }

  function getAllCountDownloadGenres($get = [])
  {
    $draw = $get['draw'];
    $start = $get['start'];
    $length = $get['length'];
    $order = $get['order'];
    $searchValue = $get['search']['value'];

    if (isset($order)) {
      switch ($order[0]['column'] + 1) {
        case 2:
          $columnSortable = 'sum_download';
          break;
        default:
          $columnSortable = 'genre_name';
          break;
      }

      $sort = $order[0]['dir'];
    } else {
      $columnSortable = 'genre_name';
      $sort = 'asc';
    }

    $searchQuery = " ";
    if ($searchValue != '') {
      $searchQuery .= " and (gs.name like '%" . $searchValue . "%' ) ";
    }

    ## Total number of records without filtering
    $sel = $this->con()->query("select count(DISTINCT gs.name) as allcount FROM genres gs");
    $records = $sel->fetch_assoc();
    $totalRecords = $records['allcount'];

    $sel = $this->con()->query("select count(DISTINCT gs.name) as allcount FROM genres gs WHERE 1 $searchQuery");
    $records = $sel->fetch_assoc();
    $totalRecordWithFilter = $records['allcount'];
//    var_dump($totalRecordWithFilter);

    $result = $this->con()->query("SELECT gs.name as genre_name, SUM(g.installs) as sum_download  FROM genres gs, games g, game_developer_genre gdg WHERE g.id = gdg.game AND gdg.developer = gs.id $searchQuery GROUP BY gs.name ORDER BY $columnSortable $sort LIMIT $start, $length");
    $games = [];
    while ($game = $result->fetch_assoc()) {
      $genre = $this->getGenreRu($game['genre_name']);
      $games[] = [
        'genre_name' => $genre,
        'sum_download' => number_format($game['sum_download']),

      ];
    }
    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordWithFilter,
      "aaData" => $games
    );
    return $response;
  }
}
