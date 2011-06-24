<?php
sleep(10);
error_reporting(E_ALL);
ini_set('display_errors',"1");

$number_per_page = 50;
$page          = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0)?intval($_REQUEST["page"]):2;
$what            = (isset($_REQUEST["what"])&&($_REQUEST["what"] == "word" || $_REQUEST["what"] == "frequency"))?$_REQUEST["what"]:"word";
$order           = (isset($_REQUEST["order"])&&($_REQUEST["order"] == "asc" || $_REQUEST["order"] == "desc"))?$_REQUEST["order"]:"asc";
$search          = (isset($_REQUEST["search"]))?mysql_real_escape_string($_REQUEST["search"]):"";
$frequency_from  = (isset($_REQUEST["frequency_from"]) && intval($_REQUEST["frequency_from"]) >= 0)?intval($_REQUEST["frequency_from"]):0;
$frequency_to    = (isset($_REQUEST["frequency_to"]) && $_REQUEST["frequency_to"] !== "")?intval($_REQUEST["frequency_to"]):(-1);
$frequency_to    = ($frequency_to < 0 && $frequency_to != -1)?(-1):$frequency_to;
$letter_id       = (isset($_REQUEST["letter"]))?intval($_REQUEST["letter"]):"";

header('Content-Type: text/html; charset=utf-8');
require_once "MysqlWrapper.php";

$db = MysqlWrapper::getInstance("localhost", "dina", "123", "simbirsoft");
$db->dbQuery("SET NAMES utf8");

$sql_query = "SELECT * FROM `dictionary`";
$res = $db->dbQuery($sql_query);
$count = mysql_num_rows($res);
$pages_fd = ceil($count/$number_per_page);

$out_string = '{"pages":'.$pages_fd.', "words": [';

$sql_query = "SELECT * FROM `dictionary` ORDER BY `$what` $order LIMIT ".(($page-1)*$number_per_page).", $number_per_page";

$res = $db->dbQuery($sql_query);

$out = array();
while ($row = mysql_fetch_assoc($res))
{
    $out[] = '{"name":"'.$row["word"].'", "count":'.$row["frequency"].'}';
}
$out_string .= implode(",",$out);
$out_string .= "]}";

echo $out_string;










$db->close();