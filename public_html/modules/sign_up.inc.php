<?php

if (!empty($_REQUEST['email'])
    and !empty($_REQUEST['password'])
    and !empty($_REQUEST['confirm-password'])
) { // если не пустые параметры емейла пароля и повторённого пароля
    $email = strip_tags($_REQUEST['email']); // удаляю теги из емейла
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
