<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 21.06.11
 * Time: 23:48
 * To change this template use File | Settings | File Templates.
 */
 
class frequencyDictionary {

    private $view;
    private $db;

    function __construct()
    {
        $this->view = new View();
        $this->db = MysqlWrapper::getInstance();
    }

    public function init()
    {

        $this->view->showTemplate("fd.header");
        $submit_link     = "index.php?module=fd&";
        $number_per_page = 50;
        $page          = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0)?intval($_REQUEST["page"]):1;
        $what            = (isset($_REQUEST["what"])&&($_REQUEST["what"] == "word" || $_REQUEST["what"] == "frequency"))?$_REQUEST["what"]:"word";
        $order           = (isset($_REQUEST["order"])&&($_REQUEST["order"] == "asc" || $_REQUEST["order"] == "desc"))?$_REQUEST["order"]:"asc";
        $search          = (isset($_REQUEST["search"]))?mysql_real_escape_string($_REQUEST["search"]):"";
        $frequency_from  = (isset($_REQUEST["frequency_from"]) && intval($_REQUEST["frequency_from"]) >= 0)?intval($_REQUEST["frequency_from"]):0;
        $frequency_to    = (isset($_REQUEST["frequency_to"]) && $_REQUEST["frequency_to"] !== "")?intval($_REQUEST["frequency_to"]):(-1);
        $frequency_to    = ($frequency_to < 0 && $frequency_to != -1)?(-1):$frequency_to;
        $letter_id       = (isset($_REQUEST["letter"]))?intval($_REQUEST["letter"]):"";

        $abc_array = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $this->view->showTemplate("fd.all", array("letter_id"=>$letter_id, "submit_link"=>$submit_link));
        foreach ($abc_array as $key => $letter)
        {
            $this->db->dbQuery('SELECT `word` FROM `dictionary` WHERE `word` LIKE "'.$letter.'%" LIMIT 1');
            $temp_array = $this->db->fetchAssocArray();
            $this->view->showTemplate("fd.abc", array("temp_array"=>$temp_array, "letter_id"=>$letter_id, "key"=>$key, "submit_link"=>$submit_link, "letter"=>$letter));
        }
        $this->view->showTemplate("fd.search", array("submit_link"=>$submit_link, "search"=>$search, "frequency_from"=>$frequency_from, "frequency_to"=>$frequency_to));
        $sql_query = "SELECT * FROM `dictionary` ";
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

        $this->db->dbQuery($sql_query);

        $word_array = $this->db->fetchAssocArray();
        $count = count($word_array);
        $pages_fd = ceil($count/$number_per_page);

        $sql_query .= "ORDER BY `$what` $order LIMIT ".(($page-1)*$number_per_page).", $number_per_page";
        $this->db->dbQuery($sql_query);

        if (!$this->db->mysqlRows())
            echo "<p>Совпадений не найдено!</p>";
        else
        {
            $result_arr = $this->db->fetchAssocArray();
            $this->view->showTemplate("fd.table", array("submit_link"=>$submit_link, "page"=>$page, "result_arr"=>$result_arr));
        }

        if ($pages_fd>1)
        {
            $this->view->showTemplate("fd.pages", array("pages_fd"=>$pages_fd, "page"=>$page, "submit_link"=>$submit_link, "what"=>$what, "order"=>$order));
        }
    }
}
