<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 03.06.11
 * Time: 4:44
 * To change this template use File | Settings | File Templates.
 */
 
class Article
{
    private $time_out = 180; // установка времени (сек.), по истечении которого скрипт заканчивает обработку файлов
    private $db;

    private $header = '';
    private $footer = '';

    private $number_of_strings;
    private $part_of_text = "";
    private $end_of_text = "";
    private $number_of_iteration = 0;

    private $pages_str = "";

    private $file_path="";

    /**
     * constructor
     * @param path of article file
     * */
    function __construct($article_file_path)
    {
        $this->file_path = $article_file_path;
        $this->db = MysqlWrapper::getInstance();

        $this->header = Utils::ApplyTemplate("templates/header.php");
        $this->footer = Utils::ApplyTemplate("templates/footer.php");
    }

    public function process($dictionary_text_array)
    {
        Utils::f_flush('<p id="process">Processing ');
         $this->pages_str .= "<p>Pages: ";
        $time_out = $this->time_out;
        $time_start = microtime(true);
        $article_file_path = $this->file_path;
        if (file_exists($article_file_path) && is_readable($article_file_path))
        {
            $file_size             = filesize($article_file_path);
            $article_file_resource = fopen($article_file_path, 'r');
            $part_size             = 0;

            if ($file_size > 32768)
            {
                do
                {
                    $article_text = fread($article_file_resource, 32768);
                    if (mb_strrchr($article_text, ' ') && mb_strrchr($article_text, ' ') != ' ')
                        $temp_string = mb_strrchr($article_text, ' ', true).' ';
                    else
                        $temp_string = $article_text;
                    $part_size += mb_strlen($temp_string);
                    fseek($article_file_resource, $part_size);
                    if (ftell($article_file_resource) == $file_size)
                        $temp_string .= "@EOF@";
                    $this->part_of_text = $temp_string;
                    $this->processing($dictionary_text_array);
                }
                while ($part_size < $file_size && (microtime(true) - $time_start) < $time_out);

                if ($part_size < $file_size)
                    echo "<p>Скрипт был прерван после $this->number_of_iteration-й итерации</p>";
            }
            else
            {
                if($file_size==0)
                    throw new Exception("Article file size must be more than zero bytes");
                $this->part_of_text = fread($article_file_resource, $file_size)."@EOF@";
                $this->processing($dictionary_text_array);

            }
            fclose($article_file_resource);
            Utils::f_flush("</p>");
            $this->pages_str .= "</p>";
            return $this->pages_str;
        }
        else
            throw new Exception("Article file is not readable!");
    }

    /**
     * set number of strings
     * @param int
     */
    public function setNumberOfStrings($n)
    {
        $this->number_of_strings = $n;
    }

    /**
     * return number of strings defined in GUI
     * @return int
     */
    public function getNumberOfStrings()
    {
        return $this->number_of_strings;
    }

    /**
     * run data processing
     * @param  array - dictionary array
     */
    public function processing($dictionary_text_array)
    {
        $matches1 = $matches2 = array();
        $string = $this->part_of_text;
        foreach ($dictionary_text_array as $word)
        {
            Utils::f_flush("|");
            $pattern = '~(^|[^\p{L}_\d])('.$word.')([^\p{L}_\d]|$)~ui';
            preg_match_all('~(^|[^\p{L}_\d])('.$word.')([^\p{L}_\d]|$)~uUi', $string, $temp_array);
            if (!empty($temp_array))
                $matches1[] = $temp_array;
            $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
        }
        foreach ($dictionary_text_array as $word)
        {
            Utils::f_flush("|");
            $pattern = '~(^|[^\p{L}_\d>])('.$word.')([^\p{L}_\d<]|$)~ui';
            preg_match_all('~(^|[^\p{L}_\d>])('.$word.')([^\p{L}_\d<]|$)~uUi', $string, $temp_array);
            $matches2[] = $temp_array;
            if (!empty($temp_array))
                $matches2[] = $temp_array;
            $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
        }
        $this->part_of_text = $string;
        $this->printing();

        $matches = array_merge($matches1, $matches2);

        $data = array();
        foreach ($matches as $arr)
        {
            $second = $arr[2];
            if(!empty($second))
                $data = array_merge($data,$second);
        }

        $frequency = array_count_values($data);

        foreach ($frequency as $key=>$value)
        {
            $sql_query = 'UPDATE `dictionary` SET `frequency`=`frequency`+'.$value.' WHERE word="'.mysql_real_escape_string($key).'"';
            $this->db->dbQuery($sql_query);
        }
        return $this->pages_str;
    }

    private function printing()
    {
        $result_string        = $this->end_of_text.str_replace("\n", "<br/>\n", $this->part_of_text);
        $main_array           = explode("\n", $result_string);

        $path = dirname(__FILE__);

        $k = 0;
        $sign_arr = array(
                    '.' , '?' , '!' , '…' ,
                    '.»', '?»', '!»', '…»',
                    '."', '?"', '!"', '…"'
                );
        while (count($main_array) > 0)
        {
            $temp_array  = array_slice($main_array, 0, $this->number_of_strings);
            $temp_string = implode("\n", $temp_array);

            if (count($main_array) > $this->number_of_strings)
            {
                $main_array = array_slice($main_array, $this->number_of_strings);
                $part_str   = ""; $arr = array();

                foreach ($sign_arr as $key => $sign)
                {
                    if (mb_strrchr($temp_string, $sign))
                    {
                        if (mb_strrchr($temp_string, $sign) != $sign."<br/>")
                        {
                            $arr[$key] = mb_strlen(mb_strrchr($temp_string, $sign, true).$sign);
                            $part_str  = "";
                        }
                        else
                            $part_str = $temp_string;
                    }
                }

                if (empty($arr))
                    $part_str = $temp_string;
                elseif (!$part_str)
                {
                    arsort($arr);
                    $number_of_sign   = array_shift(array_keys($arr));
                    $sgn              = $sign_arr[$number_of_sign];
                    $another_part_str = mb_strrchr($temp_string, $sgn);
                    $part_str         = mb_strrchr($temp_string, $sgn, true).$sgn;
                    $another_part_str = preg_replace("~^".preg_quote($sgn)."\s*(\<br/\>\n)*~", "", $another_part_str);
                    $begin_array      = explode("\n", $another_part_str);
                    $main_array       = array_merge($begin_array, $main_array);
                }
            }
            else
            {
                $is_it_eof = str_replace("@EOF@", "", $temp_string);
                if ($temp_string == $is_it_eof)
                {
                    $part_str = "";
                    $this->end_of_text = $temp_string;
                }
                else
                    $part_str = $is_it_eof;
                $main_array = array();
            }
            if ($part_str)
            {
                $k = ++$this->number_of_iteration;
                $file_name          = $path."/files00/".$k.".html";
                $resource_html_file = fopen($file_name, "w");
                fwrite($resource_html_file, $this->header . $part_str . $this->footer);
                fclose($resource_html_file);
                chmod($file_name, 0666);
                $this->pages_str .= ' <a target="_blank" href="files00/'.$k.'.html" class="link01">'.$k.'</a> ';
            }
        }
    }
}