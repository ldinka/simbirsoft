<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=windows-1251" />
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
    private $article_text_array = array();
    private $result_article     = array();

    function __construct($article_file_path)
    {
        if (file_exists($article_file_path) && is_readable($article_file_path))
        {
            $article_file_resource = fopen($article_file_path, 'r');
            $article_text = fread($article_file_resource, filesize($article_file_path));
            $article_text_array = explode(".", $article_text);
            fclose($article_file_resource);
            $this->article_text_array = $article_text_array;
        }
        else
            echo "Article file is not readable!";
    }

    public function processing($dictionary_text_array)
    {
        $before = '(^|[^\p{L}])';
        $after = '([^\p{L}]|$)';        


        foreach ($this->article_text_array as $key => $string)
            foreach ($dictionary_text_array as $word)
            {
                $pattern = '~(^|[^\p{L}_])('.$word.')([^\p{L}_]|$)~ui';
                //$pattern = '~(?<!\p{L})('.$word.')(?!\p{L})~u';

                //preg_match_all($pattern, $string, $matches);
                //f_print_r($matches);

                $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
                $result_article[$key] = $string;
            }
        $this->printing($result_article);
    }

    private function printing ($article_result_array)
    {
        foreach ($article_result_array as $string)
        {
            $string = str_replace("\n", "<br/>\n", $string);
            echo "<p>".$string."</p>\n";
        }
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
                if (preg_match("~\s~", $word))
                {
                    $word = preg_quote($word);
                    $word = preg_replace("~\s~", "///", $word);
                    $word_array = explode("///", $word);
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

if (!$_REQUEST['go'])
{
    echo '<h1>Graphical User Interface</h1>';
    echo '<form action="/" method="post" enctype="multipart/form-data">';
    echo '<p><label>Загрузить файл статьи (не более 2Мб)</label><input type="file" name="article"/></p>';
    echo '<p><label>Загрузить файл словаря (не более 2Мб)</label><input type="file" name="dictionary"/></p>';
    echo '<input type="hidden" name="go" value="1"/>';
    echo '<p></p><input type="submit"/></p>';
    echo '</form>';
}
else {
    if ($_FILES)
    {
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
    }

    $dictionary = new Dictionary($dictionary_file);
    $article    = new Article($article_file);

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

</body>
</html>