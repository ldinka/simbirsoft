<?php

header('Content-Type: text/html; charset=utf-8');   # установка кодировки для вывода скрипта

ini_set("upload_max_filesize", "2M");               # установка максимального размера загружаемого файла
ini_set("post_max_size", "4M");                     # установка максимального размера передаваемого массива данных POST

ini_set('safe_mode_gid',"1");
ini_set('safe_mode',"1");
ini_set('max_execution_time',0);
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',"1");
setlocale(LC_ALL,"ru_RU.UTF-8");

require_once("View.php");
require_once("Controller.php");
require_once("Article.php");
require_once("Dictionary.php");
require_once("Utils.php");

$controller = new Controller();
$controller->init();