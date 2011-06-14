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

    function __construct()
    {
        $this->view = new View();
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
            case "fd":
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
                break;
            case "gui":
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
