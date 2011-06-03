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
    .link01 {
        background: #eee;
        display: inline-block;
        margin: 5px;
        padding: 5px;
        text-align: center;
    }
    #preloader {
        display: none;
    }
</style>

</head>
<body>
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

class Article
{
    const HEADER = '
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
    const FOOTER = '
        </body>
        </html>
    ';

    private $article_text_array = array();
    private $result_article     = array();
    private $number_of_strings;

    function __construct($article_file_path)
    {
        if (file_exists($article_file_path) && is_readable($article_file_path))
        {
            $file_size = filesize($article_file_path);
            $article_file_resource = fopen($article_file_path, 'r');
            $part_size = 0;
            if ($file_size > 32768)
                do
                {
                    $article_text = fread($article_file_resource, 32768);
                    $temp_string = mb_strrchr($article_text, '.');
                    if ($temp_string && $temp_string != '.')
                        $temp_string = mb_strrchr($article_text, '.', true).'.';
                    elseif (mb_strrchr($article_text, ' ') && mb_strrchr($article_text, ' ') != ' ')
                        $temp_string = mb_strrchr($article_text, ' ', true).' ';
                    else
                        $temp_string = $article_text;
                    $part_size += mb_strlen($temp_string);
                    fseek($article_file_resource, $part_size);
                    $article_text_array[] = $temp_string;
                }
                while ($part_size < $file_size);
            else $article_text_array[] = fread($article_file_resource, $file_size);
            fclose($article_file_resource);
            $this->article_text_array = $article_text_array;
        }
        else
            echo "Article file is not readable!";
    }

    public function setNumberOfStrings($n)
    {
        $this->number_of_strings = $n;
    }

    public function getNumberOfStrings()
    {
        return $this->number_of_strings;
    }

    public function processing($dictionary_text_array)
    {
        foreach ($this->article_text_array as $key => $string)
            foreach ($dictionary_text_array as $word)
            {
                //[\p{L}\p{Nd}]
                $pattern = '~(^|[^\p{L}_])('.$word.')([^\p{L}_]|$)~ui';
                $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
                $result_article[$key] = $string;
            }
        $this->printing($result_article);
    }

    private function printing ($article_result_array)
    {
        $result_string = implode("", $article_result_array);
        $result_string = str_replace("\n", "<br/>\n", $result_string);
        $main_array = explode("\n", $result_string);
        $chunks = array_chunk($main_array, $this->number_of_strings);
        echo "<p>Pages: ";
        foreach ($chunks as $key => $chunk)
        {
            $file_name = "./files00/".($key+1).".html";
            $resource_html_file = fopen($file_name, "w");
            fwrite($resource_html_file, $this::HEADER . implode("\n", $chunk) . $this::FOOTER);
            fclose($resource_html_file);
            chmod($file_name, 0666);
            echo ' <a target="_blank" href="files00/'.($key+1).'.html" class="link01">'.($key+1).'</a> ' ;
        }
        echo "</p>";
    }
}

class Dictionary
{
    private $dictionary_text_array;

    public function getDictionaryTextArray()
    {
        return $this->dictionary_text_array;
    }

    function __construct($dictionary_file_path)
    {
        if (file_exists($dictionary_file_path) && is_readable($dictionary_file_path))
        {
            $dictionary_array = file($dictionary_file_path);
            if (count($dictionary_array) > 100000)
            {
                echo "<p>Количество строк превышает допустимое значение длины. Файл словаря будет обрезан до допустимый длины.</p>";
                $dictionary_array = array_slice($dictionary_array, 0, 100000);
            }

            $dictionary_array = array_unique($dictionary_array);

            $temp_array = array();
            foreach ($dictionary_array as $word)
            {
                $word = trim($word);
                if (preg_match("~[^\p{L}^_^ ]~ui", $word))
                {
                    $word = preg_quote($word);
                    $word = preg_replace("~[^\p{L}^_^ ]~ui", "", $word);
                    $word_array = preg_split("~\s~", $word);
                    if (!empty($word_array))
                    {
                        foreach ($word_array as $word_array_item)
                        {
                            $word_array_item = trim($word_array_item);
                            if ($word_array_item)
                                $temp_array[] = $word_array_item;
                        }
                    }
                }
                elseif ($word) $temp_array[] = $word;
            }

            $dictionary_array = $temp_array;
            $dictionary_array = array_unique($dictionary_array);

            $dictionary_text = implode(" ", $dictionary_array);
            $new_text = wordwrap($dictionary_text, 32000, "///");
            $new_text = str_replace(" ", "|", $new_text);
            $dictionary_text_array = explode("///", $new_text);
            $dictionary_text_array = array_unique($dictionary_text_array);
            $this->dictionary_text_array = $dictionary_text_array;
        }
        else
            echo "Dictionary file is not readable!";
    }
}

$time_start = microtime(true);

echo '<h1>Graphical User Interface</h1>';
echo '<form action="/" method="post" enctype="multipart/form-data">';
echo '<p><label>Загрузить файл статьи (не более 2Мб)</label><input type="file" name="article"/></p>';
echo '<p><label>Загрузить файл словаря (не более 2Мб)</label><input type="file" name="dictionary"/></p>';
echo '<p><label>Количество выводимых строк (10-100000)</label><input type="text" name="number_of_strings" value="'.(isset($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):100).'"/></p>';
echo '<input type="hidden" name="go" value="1"/>';
echo '<p><input type="submit" id="submit" onClick="viewPreloader()"/><img id="preloader" src="preloader.gif" /></p>';
echo '</form>';

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

    $files_dir = "./files00/";
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
        chmod($dictionary_file, 0777);
    }

    if (!move_uploaded_file($_FILES["article"]["tmp_name"], $article_file))
    {
        echo "<p>Ошибка загрузки файла статьи!</p>";
        exit;
    }
    else chmod($article_file, 0777);

    $dictionary = new Dictionary($dictionary_file);
    $article    = new Article($article_file);
    $article->setNumberOfStrings(intval($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):1000);

    $article_result_array = $article->processing($dictionary->getDictionaryTextArray());

}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "<p>Скрипт выполнялся $time секунд\n</p>";

//unlink($dictionary_file);
//unlink($article_file);
//rmdir($files_dir);


function f_print_r ($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

?>
<script type="text/javascript">
	function viewPreloader()
    {
        document.getElementById('submit').style.display = 'none';
		document.getElementById('preloader').style.display = 'block';
	}
</script>
</body>
</html>