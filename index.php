<?php

header('Content-Type: text/html; charset=utf-8');   # установка кодировки для вывода скрипта

ini_set("upload_max_filesize", "2M");               # установка максимального размера загружаемого файла
ini_set("post_max_size", "4M");                     # установка максимального размера передаваемого массива данных POST

ini_set('safe_mode_gid',"1");
ini_set('safe_mode',"1");
ini_set('max_execution_time',0);
ini_set("output_buffering", "0");
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',"1");
setlocale(LC_ALL,"ru_RU.UTF-8");

require_once("View.php");
require_once("Controller.php");
require_once("Article.php");
require_once("Dictionary.php");
require_once("Utils.php");
$link = mysql_connect("localhost", "dina", "123")
    or die("Could not connect: " . mysql_error());
mysql_select_db("simbirsoft", $link) or die ("Can't use foo : ".mysql_error());
mysql_query("SET NAMES utf8");
$controller = new Controller();
$controller->init();









$number_per_page = 100;
$number = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0)?intval($_REQUEST["page"]):1;
$what = (isset($_REQUEST["what"])&&($_REQUEST["what"] == "word" || $_REQUEST["what"] == "frequency"))?$_REQUEST["what"]:"word";
$order = (isset($_REQUEST["order"])&&($_REQUEST["order"] == "asc" || $_REQUEST["order"] == "desc"))?$_REQUEST["order"]:"asc";
$search = (isset($_REQUEST["search"]))?mysql_real_escape_string($_REQUEST["search"]):"";
$frequency_from = (isset($_REQUEST["frequency_from"]) && intval($_REQUEST["frequency_from"]) >= 0)?intval($_REQUEST["frequency_from"]):0;
$frequency_to   = (isset($_REQUEST["frequency_to"]) && $_REQUEST["frequency_to"] !== "")?intval($_REQUEST["frequency_to"]):(-1);
$frequency_to   = ($frequency_to < 0 && $frequency_to != -1)?(-1):$frequency_to;
$letter_id = (isset($_REQUEST["letter"]))?intval($_REQUEST["letter"]):"";

$abc_array = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

echo "<p>";
$temp_array = array();
foreach ($abc_array as $key => $letter)
{
    $mysql_link = mysql_query('SELECT `word` FROM `dictionary` WHERE `word` LIKE "'.$letter.'%" ORDER BY word ASC');
    $temp_array = mysql_fetch_assoc($mysql_link);
    if (!empty($temp_array))
    {
        echo ' <a href="index.php?letter='.$key.'" class="link01 link02">'.$letter.'</a> ';
    }
    else
        echo ' <span class="link02">'.$letter.'</span> ';
}
echo "</p>";



echo '<form action="index.php" method="post">';
echo '<p><label>Поиск:</label><input type="text" value="'.strip_tags($search).'" name="search"/></p>';
echo '<p><label>Частота появления:</label>
         от<input type="text" value="'.$frequency_from.'" name="frequency_from"/>
         до<input type="text" value="'.(($frequency_to != -1)?$frequency_to:"").'" name="frequency_to"/>
         <input type="submit" value="Поиск"/>
      </p>';
echo '</form>';
echo "<p>Frequency dictionary:</p>";



$sql_query = "SELECT * FROM `dictionary` ";
$submit_link = "index.php?";

if ($letter_id !== "")
{
    $sql_query .= ' WHERE `word` LIKE "'.$abc_array[$letter_id].'%" ';
    $submit_link .= "letter=$letter_id&";
}
elseif ($search || $frequency_from > 0 || $frequency_to >= 0)
{
    $sql_query .= " WHERE ";
    if ($search)
    {
        $sql_query .= ' `word` LIKE "'.$search.'%" AND ';
        $submit_link .= "search=$search&";
    }
    if ($frequency_to != -1 && $frequency_from > $frequency_to)
    {
        $temp_var = $frequency_from;
        $frequency_from = $frequency_to;
        $frequency_to = $temp_var;
    }
    $sql_query .= ' `frequency`>='.$frequency_from.' ';
    $submit_link .= "frequency_from=$frequency_from&";
    if ($frequency_to != -1)
    {
        $sql_query .= ' AND `frequency`<='.$frequency_to.' ';
        $submit_link .= "frequency_to=$frequency_to&";
    }
}

$temp_link = mysql_query($sql_query);

$word_array = array();
while($row = mysql_fetch_assoc($temp_link))
    $word_array[] = $row;
$count = count($word_array);
$pages_fd = ceil($count/$number_per_page);

if ($pages_fd>1)
{
    echo "<p>";
    for ($i=1; $i<=$pages_fd; $i++)
        echo ' <a href="'.$submit_link.'page='.$i.'&what='.$what.'&order='.$order.'" class="link01">'.$i.'</a> ';
    echo "</p>";
}

$sql_query .= "ORDER BY `$what` $order LIMIT ".(($number-1)*$number_per_page).", $number_per_page";
$result = mysql_query($sql_query);

if (!mysql_num_rows($result))
    echo "<p>Совпадений не найдено!</p>";
else
{
    echo '
    <table>
        <tr>
            <th>
                <p>Слово
                    <a href="'.$submit_link.'page='.$number.'&what=word&order=asc">прямо</a>
                    <a href="'.$submit_link.'page='.$number.'&what=word&order=desc">обратно</a>
                </p>
            </th>
            <th>
                <p>Частота
                    <a href="'.$submit_link.'page='.$number.'&what=frequency&order=asc">прямо</a>
                    <a href="'.$submit_link.'page='.$number.'&what=frequency&order=desc">обратно</a>
                </p>
            </th>
        </tr>
    ';
    while($row = mysql_fetch_assoc($result))
        echo "<tr><td><p><label>$row[word]</label></p><td><p>$row[frequency]</p></td></tr>";
    echo '</table>';
}

mysql_close($link);