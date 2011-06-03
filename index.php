<?php

header('Content-Type: text/html; charset=utf-8');

ini_set("upload_max_filesize", "2M");
ini_set("post_max_size", "4M");

ini_set('safe_mode_gid',"1");
ini_set('safe_mode',"1");
ini_set('max_execution_time',0);
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',"1");
setlocale(LC_ALL,"ru_RU.UTF-8");

require_once("Article.php");
require_once("Dictionary.php");
require_once("Utils.php");

$time_start = microtime(true);

echo Utils::init_header();

echo Utils::init_gui();


if (isset($_REQUEST['go']))
{
    if (intval($_REQUEST["number_of_strings"]) < 10 || intval($_REQUEST["number_of_strings"]) > 100000)
    {
        echo "<p>Укажите количество выводимых строк от 10 до 100000!</p>";
        exit;
    }
    if ($_FILES["dictionary"]["error"] || $_FILES["article"]["error"])
    {
        switch ($_FILES["dictionary"]["error"])
        {
            case 1:
                echo "<p>Недопустимый размер файла словаря!</p>";
                break;
            case 2:
                echo "<p>Недопустимый размер файла словаря!</p>";
                break;
            case 3:
                echo "<p>Загружаемый файл словаря был получен только частично. </p>";
                break;
            case 4:
                echo "<p>Файл словаря не был загружен.</p>";
                break;
        }
        switch ($_FILES["article"]["error"])
        {
            case 1:
                echo "<p>Недопустимый размер файла статьи!</p>";
                exit;
            case 2:
                echo "<p>Недопустимый размер файла статьи!</p>";
                exit;
            case 3:
                echo "<p>Загружаемый файл статьи был получен только частично. </p>";
                exit;
            case 4:
                echo "<p>Файл статьи не был загружен.</p>";
                exit;
            default:
                exit;
        }
    }

    $path = dirname(__FILE__);

    $files_dir = $path."/files00/";
    $dictionary_file = $files_dir."dictionary.txt";
    $article_file = $files_dir."article.txt";

    if (!file_exists($files_dir))
    {
        mkdir($files_dir);
        chmod($files_dir, 0777);
    }
    if (!move_uploaded_file($_FILES["dictionary"]["tmp_name"], $dictionary_file))
    {
        echo "<p>Ошибка загрузки файла словаря!</p>";
        exit;
    }
    else
    {
        chmod($dictionary_file, 0666);
    }

    if (!move_uploaded_file($_FILES["article"]["tmp_name"], $article_file))
    {
        echo "<p>Ошибка загрузки файла статьи!</p>";
        exit;
    }
    else chmod($article_file, 0666);

    $dictionary = new Dictionary($dictionary_file);
    $article    = new Article($article_file);
    $article->setNumberOfStrings(intval($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):1000);

    $article_result_array = $article->processing($dictionary->getDictionaryTextArray());

    //unlink($dictionary_file);
    //unlink($article_file);
    //rmdir($files_dir);
}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "<p>Скрипт выполнялся $time секунд\n</p>";

echo Utils::init_footer();