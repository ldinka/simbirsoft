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
    private $db;

    function __construct()
    {
        $this->view = new View();
        $this->db = MysqlWrapper::getInstance();
    }

    public function init()
    {
        $time_start = microtime(true);

        $this->view->showHeader();

        try
        {
            $this->run();
        }
        catch (Exception $e)
        {
            echo '<p><span class="error">'.$e->getMessage().'</span></p>';
        }

        $time_end = microtime(true);
        $time     = $time_end - $time_start;
        echo "<p>Скрипт выполнялся $time секунд\n</p>";

        $this->view->showFooter();

    }

    private function run()
    {
        $module = isset($_REQUEST["module"])?$_REQUEST["module"]:"";
        switch ($module)
        {
            case "fd":  #frequency dictionary page
                echo '
                    <div id="header">
                    <a href="index.php?module=gui" class="link03">Graphical User Interface</a>
                    <h1>Frequency dictionary</h1>
                    </div>
                ';
                $submit_link = "index.php?module=fd&";
                $number_per_page = 50;
                $number = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0)?intval($_REQUEST["page"]):1;
                $what = (isset($_REQUEST["what"])&&($_REQUEST["what"] == "word" || $_REQUEST["what"] == "frequency"))?$_REQUEST["what"]:"word";
                $order = (isset($_REQUEST["order"])&&($_REQUEST["order"] == "asc" || $_REQUEST["order"] == "desc"))?$_REQUEST["order"]:"asc";
                $search = (isset($_REQUEST["search"]))?mysql_real_escape_string($_REQUEST["search"]):"";
                $frequency_from = (isset($_REQUEST["frequency_from"]) && intval($_REQUEST["frequency_from"]) >= 0)?intval($_REQUEST["frequency_from"]):0;
                $frequency_to   = (isset($_REQUEST["frequency_to"]) && $_REQUEST["frequency_to"] !== "")?intval($_REQUEST["frequency_to"]):(-1);
                $frequency_to   = ($frequency_to < 0 && $frequency_to != -1)?(-1):$frequency_to;
                $letter_id = (isset($_REQUEST["letter"]))?intval($_REQUEST["letter"]):"";

                $abc_array = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

                $active_all = "";
                if ($letter_id === "")
                    $active_all = " active";
                echo '<p><a href="'.$submit_link.'" class="link01 link02'.$active_all.'">все</a> ';

                foreach ($abc_array as $key => $letter)
                {
                    $this->db->dbQuery('SELECT `word` FROM `dictionary` WHERE `word` LIKE "'.$letter.'%" LIMIT 1');

                    $temp_array = $this->db->fetchAssocArray();
                    if (!empty($temp_array))
                    {
                        if ($letter_id === $key)
                            $active = " active";
                        else $active = "";
                        echo ' <a href="'.$submit_link.'letter='.$key.'" class="link01 link02'.$active.'">'.$letter.'</a> ';
                    }
                    else
                        echo ' <span class="link02">'.$letter.'</span> ';
                }
                echo "</p>";



                echo '<form action="'.$submit_link.'" method="post">';
                echo '<p><label>Поиск:</label><input type="text" value="'.strip_tags($search).'" name="search"/></p>';
                echo '<p><label>Частота появления:</label>
                         от<input type="text" value="'.$frequency_from.'" name="frequency_from"/>
                         до<input type="text" value="'.(($frequency_to != -1)?$frequency_to:"").'" name="frequency_to"/>
                         <input type="submit" value="Поиск"/>
                      </p>';
                echo '</form>';

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

                $sql_query .= "ORDER BY `$what` $order LIMIT ".(($number-1)*$number_per_page).", $number_per_page";
                $this->db->dbQuery($sql_query);

                if (!$this->db->mysqlRows())
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
                    $result_arr = $this->db->fetchAssocArray();
                    foreach ($result_arr as $row)
                        echo "<tr><td><p><label>$row[word]</label></p><td><p>$row[frequency]</p></td></tr>";
                    echo '</table>';
                }

                if ($pages_fd>1)
                {
                    echo "<p>";
                    for ($i=1; $i<=$pages_fd; $i++)
                    {
                        if ($i == $number)
                            $active = " active";
                        else $active = "";
                        echo ' <a href="'.$submit_link.'page='.$i.'&what='.$what.'&order='.$order.'" class="link01 link02'.$active.'">'.$i.'</a> ';
                    }
                    echo "</p>";
                }

                break;
            case "gui":  #graphical user interface page
            default:
                $this->view->showGUI();
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
                        $this->view->showError($err);
                }
                break;
        }
    }
}
