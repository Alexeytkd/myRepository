<?php

$sql = "create table users
(
id            int auto_increment
primary key,
user_name     varchar(128) null,
email         varchar(128) not null,
user_password varchar(128) not null,
last_login    int(10)      null,
created_at    int(10)      null,
roles         varchar(128) null,
avatar_src    varchar(255) null
);";
$conn->query($sql);
if($conn->errsor) {
    print_r($conn->error);
    die;
}

$sql = "create table user_request
(
id          int auto_increment
primary key,
asset_id    int         not null,
user_link   varchar(64) null,
comment     text        null,
status      int(1)      null,
created_by  int         null,
updated_by  int         null,
created_at  int(10)     null,
updated_at  int(10)     null,
category    int         null,
uid         bigint(20)  null,
total_spent float       null,
tags        text        null
);";
$conn->query($sql);
if($conn->error) {
    print_r($conn->error);
    die;
}

$sql = "create table app_settings
(
ID    int auto_increment
primary key,
name  varchar(128) not null,
env   varchar(128) null,
value text         null
);";
$conn->query($sql);
if($conn->error) {
    print_r($conn->error);
    die;
}

$categoryJson = '{"4":"Assets","7":"Christmas","2":"Clothes","3":"Easter","5":"Gameplay","8":"Halloween","6":"Release theme","1":"Scenery","10":"St. Patricks","9":"St.Valentine","11":"Stylist"}';
$sql = "insert into app_settings (name, value) values ('categories', '" . $categoryJson . "')";
$conn->query($sql);
if($conn->error) {
    print_r($conn->error);
    die;
}

$filename = '../source/testJsonDataToSort.txt';
$f = file_get_contents($filename);
$user_r = json_decode($f, true);
foreach($user_r as $k => $r) {
    $r['tags'] = json_encode($r['tags']);
    $sql = 'insert into user_request (id, asset_id, user_link, comment, status, created_by, updated_by, created_at, updated_at, category, uid, total_spent, tags) values (' . $r['id'] . ', ' . $r['asset_id'] . ', "' . $r['user_link'] . '","' . str_replace('"', '', ($r['comment'])) . '",' . $r['status'] . ',' . $r['created_by'] . ',' . $r['updated_by'] . ',' . $r['created_at'] . ',' . $r['updated_at'] . ',' . $r['category'] . ',' . $r['uid'] . ',' . $r['total_spent'] . ',\'' . $r['tags'] . '\')';
    $conn->query($sql);
    if($conn->error) {
        print_r($conn->error);
        die;
    }
}


header('Location: /');