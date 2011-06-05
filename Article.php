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
    private $header = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <style>
            html, body {
                font: 12px/16px Arial;
            }
            h1 {
                color: #00438F;
                font-size: 16px;
                padding: 20px 40px;
            }
            label {
                font-size: 14px;
                display: inline-block;
                width: 300px;
            }
            p {
                margin: 6px 0;
            }
            b i, i b {
                background: #FF9393;
            }
        </style>

        </head>
        <body>
    ';
    private $footer = '
        </body>
        </html>
    ';

    private $article_text_array = array();
    private $result_article     = array();
    private $number_of_strings;

    private $pages_str = "";

    /**
     * constructor
     * @param path of article file
     * */
    function __construct($article_file_path)
    {
        if (file_exists($article_file_path) && is_readable($article_file_path))
        {
            $file_size             = filesize($article_file_path);
            $article_file_resource = fopen($article_file_path, 'r');
            $part_size             = 0;
            
            if ($file_size > 32768)
                do
                {
                    $article_text = fread($article_file_resource, 32768);
                    if (mb_strrchr($article_text, ' ') && mb_strrchr($article_text, ' ') != ' ')
                        $temp_string = mb_strrchr($article_text, ' ', true).' ';
                    else
                        $temp_string = $article_text;
                    $part_size += mb_strlen($temp_string);
                    fseek($article_file_resource, $part_size);
                    $article_text_array[] = $temp_string;
                }
                while ($part_size < $file_size);
            else
            {
                if($file_size==0)
                    throw new Exception("Article file size must be more than zero bytes");
                $article_text_array[] = fread($article_file_resource, $file_size);
            }
            fclose($article_file_resource);
            $this->article_text_array = $article_text_array;
        }
        else
            echo "Article file is not readable!";
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
        foreach ($this->article_text_array as $key => $string)
        {
            foreach ($dictionary_text_array as $word)
            {
                $pattern = '~(^|[^\p{L}_\d])('.$word.')([^\p{L}_\d]|$)~ui';
                $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
                $this->result_article[$key] = $string;
            }
            foreach ($dictionary_text_array as $word)
            {
                $pattern = '~(^|[^\p{L}_\d>])('.$word.')([^\p{L}_\d<]|$)~ui';
                $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
                $this->result_article[$key] = $string;
            }
        }
        $this->printing();
        return $this->pages_str;
    }

    private function printing()
    {
        $article_result_array = $this->result_article;
        $result_string        = implode("", $article_result_array);
        $result_string        = str_replace("\n", "<br/>\n", $result_string);
        $main_array           = explode("\n", $result_string);

        $path = dirname(__FILE__);

        $this->pages_str = "<p>Pages: ";

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
                $part_str   = $temp_string;
                $main_array = array();
            }
            if ($part_str)
            {
                $k++;
                $file_name          = $path."/files00/".$k.".html";
                $resource_html_file = fopen($file_name, "w");
                fwrite($resource_html_file, $this->header . $part_str . $this->footer);
                fclose($resource_html_file);
                chmod($file_name, 0666);
                $this->pages_str .= ' <a target="_blank" href="files00/'.$k.'.html" class="link01">'.$k.'</a> ' ;
            }
        }
        $this->pages_str .= "</p>";
    }
}