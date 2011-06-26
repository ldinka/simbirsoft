<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 05.06.11
 * Time: 23:08
 * To change this template use File | Settings | File Templates.
 */
 
class Controller
{
    private $view;
    private $fd;

    function __construct()
    {
        $this->view = new View();
        $this->fd = new FrequencyDictionary();
        $this->db = MysqlWrapper::getInstance();
    }

    public function init()
    {
        try
        {
            $this->run();
        }
        catch (Exception $e)
        {
            $this->view->showTemplate("error", array("errors"=>$e->getMessage()));
        }
    }

    private function run()
    {
        $ajax = (isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] == "yes")?$_REQUEST["ajax"]:"no";
        if ($ajax == "yes")
        {
            $submit_link     = "index.php?module=fd&";
            $filter = isset($_REQUEST["filter"])?addslashes($_REQUEST["filter"]):"";

            $number_per_page = 50;
            $page            = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0)?intval($_REQUEST["page"]):1;
            $what            = (isset($_REQUEST["what"])&&($_REQUEST["what"] == "word" || $_REQUEST["what"] == "frequency"))?$_REQUEST["what"]:"word";
            $order           = (isset($_REQUEST["order"])&&($_REQUEST["order"] == "asc" || $_REQUEST["order"] == "desc"))?$_REQUEST["order"]:"asc";
            $search          = (isset($_REQUEST["search"]))?mysql_real_escape_string($_REQUEST["search"]):"";
            $frequency_from  = (isset($_REQUEST["frequency_from"]) && intval($_REQUEST["frequency_from"]) >= 0)?intval($_REQUEST["frequency_from"]):0;
            $frequency_to    = (isset($_REQUEST["frequency_to"]) && $_REQUEST["frequency_to"] !== "")?intval($_REQUEST["frequency_to"]):(-1);
            $frequency_to    = ($frequency_to < 0 && $frequency_to != -1)?(-1):$frequency_to;
            $letter_id       = (isset($_REQUEST["letter"]) && $_REQUEST["letter"] != "all")?intval($_REQUEST["letter"]):"all";

            $abc_array = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

            $sql_query = "SELECT * FROM `dictionary` ";
            $submit_link .= "letter=$letter_id&";

            switch ($filter)
            {
                case "letter":
                case "sorting":
                case "paginator":
                    if ($letter_id !== "all")
                    {
                        $sql_query .= ' WHERE `word` LIKE "'.$abc_array[$letter_id].'%" ';
                    }
                    break;
                default:
                    break;
            }
            $this->db->dbQuery($sql_query);
            $word_array = $this->db->fetchAssocArray();
            $count = count($word_array);
            $pages_fd = ceil($count/$number_per_page);

            $sql_query .= "ORDER BY `$what` $order LIMIT ".(($page-1)*$number_per_page).", $number_per_page";

            $res = $this->db->dbQuery($sql_query);

            $out_string = "";
            $out_string .= '{"pages":'.$pages_fd.', ';
            $out_string .= ' "submit_link":"'.$submit_link.'", ';
            $out_string .= ' "words": [';

            if (!$this->db->mysqlRows())
                echo '{"error":"Совпадений не найдено!"}';
            else
            {
                $out = array();
                while ($row = mysql_fetch_assoc($res))
                {
                    $out[] = '{"name":"'.$row["word"].'", "count":'.$row["frequency"].'}';
                }
                $out_string .= implode(",",$out);
                $out_string .= "]}";

                echo $out_string;
            }
            exit();
        }
        $time_start = microtime(true);
        $this->view->showTemplate("header");


        $module = isset($_REQUEST["module"])?$_REQUEST["module"]:"";
        switch ($module)
        {

            case "fd":  #frequency dictionary page
                $this->fd->init();
                break;
            case "gui":  #graphical user interface page
            default:
                $this->view->showTemplate("gui");
                $err = array();
                if (isset($_REQUEST['go']))
                {

                    if (intval($_REQUEST["number_of_strings"]) < 10 || intval($_REQUEST["number_of_strings"]) > 100000)
                        $err[] = "Укажите количество выводимых строк от 10 до 100000!";
                    if ($_FILES["dictionary"]["error"] || $_FILES["article"]["error"])
                    {
                        switch ($_FILES["dictionary"]["error"])
                        {
                            case 1:
                                $err[] = "Недопустимый размер файла словаря!";
                                break;
                            case 2:
                                $err[] = "Недопустимый размер файла словаря!";
                                break;
                            case 3:
                                $err[] = "Загружаемый файл словаря был получен только частично.";
                                break;
                            case 4:
                                $err[] = "Файл словаря не был загружен.";
                                break;
                        }
                        switch ($_FILES["article"]["error"])
                        {
                            case 1:
                                $err[] = "Недопустимый размер файла статьи!";
                                break;
                            case 2:
                                $err[] = "Недопустимый размер файла статьи!";
                                break;
                            case 3:
                                $err[] = "Загружаемый файл статьи был получен только частично.";
                                break;
                            case 4:
                                $err[] = "Файл статьи не был загружен.";
                                break;
                        }
                    }

                    if (empty($err))
                    {
                        $path = dirname(__FILE__);

                        $files_dir       = $path."/files00/";
                        $dictionary_file = $files_dir."dictionary.txt";
                        $article_file    = $files_dir."article.txt";

                        if (!file_exists($files_dir))
                        {
                            mkdir($files_dir);
                            chmod($files_dir, 0777);
                        }

                        if (!move_uploaded_file($_FILES["article"]["tmp_name"], $article_file))
                            throw new Exception("Ошибка загрузки файла статьи!");
                        else
                        {
                            chmod($article_file, 0666);
                            Utils::f_flush("<p>Файл статьи успешно загружен на сервер.</p>");
                        }

                        if (!move_uploaded_file($_FILES["dictionary"]["tmp_name"], $dictionary_file))
                            throw new Exception("Ошибка загрузки файла словаря!");
                        else chmod($dictionary_file, 0666);


                        $dictionary = new Dictionary($dictionary_file);
                        $article    = new Article($article_file);
                        $article->setNumberOfStrings(intval($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):1000);
                        $pages = $article->process($dictionary->getDictionaryTextArray());

                        $this->view->showPages($pages);
                    }
                    else
                        //$this->view->showError($err);
                        $this->view->showTemplate("error", array("errors"=>$err));
                }
                break;
        }
        $time_end = microtime(true);
        $time     = $time_end - $time_start;
        $this->view->showTemplate("time", array("time"=>$time));
        $this->view->showTemplate("footer");
    }
}
