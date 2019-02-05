<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 24.01.19
 * Time: 11:47
 */
include_once ('../libs/func.php');
$conn = getDatabaseConnect();

ini_set('display_errors' , 1);
if (sizeof($_REQUEST)) {
    if (!empty($_REQUEST['email'])
        and !empty($_REQUEST['password'])
        and !empty($_REQUEST['confirm-password'])
    ) { // если не пустые параметры емейла пароля и повторённого пароля
        $email = strip_tags($_REQUEST['email']); // удаляю теги из емейлапше ыефегы

        $isEmail = $conn->query('select id from users where email = "' . $email . '"'); // ищу пользователя в базе по введенному емейлу
        if ($isEmail->num_rows > 0) { // если нахожу то верну ошибку
            $errorMessage .= 'Such email is already taken!';
        }

        if (!$errorMessage) { // если нет ошибок
            if (!$errorMessage && $_REQUEST['password'] == $_REQUEST['confirm-password']) { // если пароли совпадают
                $password = md5($_REQUEST['password']); // шифрую пароль
                $current_time = time();
                # сохраняю в базу нового пользователя
                $conn->query('insert into users (email, user_password, created_at, last_login) values ("' . $email . '", "' . $password . '", ' . $current_time . ', ' . $current_time . ')');
                $userResource = $conn->query('select id from users where email = "' . $email . '"'); // получаю id соханённого пользователя
                $user = $userResource->fetch_assoc(); // представление результата в виде массива
                auth($conn, $user['id']); // вызываю метод авторизации
                header('Location: /profile'); // редирект на главную
            } else {
                $errorMessage .= 'Passwords not equal!'; // ошибка пароли не совпадают
            }
        }
    }

}

echo "
<meta charset=" . '<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="http://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    
    <title>Title</title>
</head>
<body>
<form method="post" enctype="multipart/form-data">
<div class=" form-group from-wrapper " > 
         <input placeholder="User name"  class="form-control " name="user_name">
         <input placeholder="Email"  class="form-control " name="email">
         <input placeholder="password"  class="form-control " name="password" type="password">
         <input placeholder="confirm password"  class="form-control " name="confirm-password" type="password">
         <input   class="form-control " name="avatar" type="file">
       <div class="radio"></div>     
          <label class="radio-inline" for="male">Male
           <input type="radio"   name="gebder" value="1" id=" Male">
            </label>
       </div>
       <div class="radio">
            <label for="male" class="radio-inline" >Famale
            <input type="radio"  class=" " name="gebder" value="2" id=" Famale"> 
            </label>
        </div>
            <input type="submit" value="Send" class="btn btn-primary">
    </div>
</form>
</body>
</html>';