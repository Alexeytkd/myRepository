<?php

/**
 * @param $filename
 * @return array
 */
function getEnvs($filename)
{
    $envFile = file_get_contents($filename); // получение содержимого файла .env

    $envData = explode('', $envFile); // построчное разбиение на элементы массива
    $env = [];
    foreach ($envData as $param) { // цикл по строкам
        $item = explode('=', $param); // разбиение по знаку =
        $env[trim($item[0])] = trim($item[1]); // формирование массива $env с удалением лишних пробелов
    }

    return $env; // возвращаемое функцией значение
}

/**
 * @return mysqli
 */
function getDatabaseConnect()
{
    $conn = new mysqli('localhost', 'alexey', '1234', 'homestead'); // создание подключения
    // Check connection
    if ($conn->connect_error) { // проверка наличие ошибки
        die("Connection failed: " . $conn->connect_error); // вывод ошибки
    }

    if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'migration') { // проверка приходит ли параметр /?do=migration
        include_once('../source/migration.php'); // подключение файла миграций
    }

    $sql = 'select count(*) as user_count from users'; // формирование агрегатного запроса к базе (подсчет количества пользователей)
    $checkSql = $conn->query($sql); // исполнение запроса
    if ($checkSql) { // проверка результата
        $usersCount = $checkSql->fetch_assoc(); // представление результата в виде массива
    }


    if ($conn->error) { // проверка ошибок запроса
        print_r($conn->error); // вывод ошибок (если база пустая ошибка скажет что таблицы users не существуе)
        echo '<br>No data in database. Do you want to <a href="/?do=migration">run migration</a>?'; // и тогда выводим ссылку которая запустит миграцию
        die;
    }

    return $conn; // вовзврат соендинения с базой
}

/**
 * @param $conn
 * @param int $id
 */
function auth($conn, $id)
{
    $current_time = time(); // записываю в переменную unix время
    $hash = base64_encode(substr($current_time, 0, strlen($current_time) - strlen($id)) . $id . '1984' . strlen($id)); // создаю хеш строку
    $_SESSION['session_hash'] = $hash; // записываю хеш строку в сессию
    setcookie('session_hash', $hash); // записываю хеш строку в кукис
    $conn->query('update users set last_login = "' . $current_time . '" where id = ' . $id); // обновляю в базе время последнего логина пользователя
}

/**
 * @param array $categoryList
 * @param int $categoryNumber
 * @param int $item
 * @return string
 */
function showCategoriesSelector(array $categoryList, int $categoryNumber, int $item)
{
    # циклом по категориям формирую селектор категорий для каждой строки таблицы
    $categorySelector = '<form method="get">
    <input type="hidden" value="' . $item . '" name="item" /> <!-- скрытое поле с id элемента -->
    <select name="update_cat" onchange="this.form.submit();">';
    foreach ($categoryList as $key => $cat) {
        $condition = $categoryNumber == $key ? 'selected' : '';
        $categorySelector .= '<option value="' . $key . '" ' . $condition . '> ' . $cat . '</option>';
    }
    $categorySelector .= '</select></form>';

    return $categorySelector;
}

/**
 * @param array $arr
 * @param bool $descStatus
 * @return array
 */
function sorter(array $arr, bool $descStatus)
{
    if (!isset($_REQUEST['sort'])) return $arr; // если нет параметра сортировки возвращаю исходный массив без изменений

    # сортировка массива на основе параметров запроса
    switch (str_replace('-', '', $_REQUEST['sort'])) {
        case 'category':
            if ($descStatus) {
                function mnsort($a, $b)
                {
                    return strnatcmp($b['category'], $a['category']); // применение человекоподобного алгоритма для сортировки строк
                }
            } else {
                function mnsort($a, $b)
                {
                    return strnatcmp($a['category'], $b['category']);
                }
            }
            break;
        default:
            if ($descStatus) {
                function mnsort($a, $b)
                {
                    $sortName = str_replace('-', '', $_REQUEST['sort']); // удаление лишнего символа для получения текущего поля сортировки
                    return $b[$sortName] > $a[$sortName]; // сравнение числовых значений для сортировки
                }
            } else {
                function mnsort($a, $b)
                {
                    return $a[$_REQUEST['sort']] > $b[$_REQUEST['sort']];
                }
            }
            break;
    }
    usort($arr, 'mnsort'); // применение сортировки инициализированной в switch функции

    return $arr;
}

/**
 * @param $conn
 * @param int $item
 * @param int $update_cat
 */
function changeCategory($conn, int $item, int $update_cat)
{
    $conn->query('update user_request set category = "' . $update_cat . '" where id = ' . $item); // записываю новое значение категории в базу данных
    header('Location: /'); // переадресация на главную страницу
}

/**
 * @return bool|string|void
 */
function getUserId()
{
    if(empty($_COOKIE['session_hash'])) return; // если нет кукис возвращаю null
    $session_hash = explode('1984', base64_decode($_COOKIE['session_hash'])); // расшифровка кукис и разбиение на параметры
    return substr($session_hash[0], strlen($session_hash[0]) - $session_hash[1]); // возвращаю id пользователя
}

/**
 * @param $conn
 * @param $userId
 * @return mixed
 */
function getUserData($conn, $userId)
{
    $userData = $conn->query('select * from users where id = ' . $userId); // получаю все данные пользователя из базы
    return $userData->fetch_assoc(); // представление результата в виде массива
}