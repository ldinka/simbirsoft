<?php

header('Content-Type: text/html; charset=utf-8');

ini_set('max_execution_time',0);
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors',"1");

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
        foreach ($this->article_text_array as $key => $string)
            foreach ($dictionary_text_array as $word)
            {
                $pattern = '~(^|\s)('.$word.')(\s|$)~u';
                $string = preg_replace($pattern, "\\1<b><i>\\2</i></b>\\3" , $string);
                $result_article[$key] = $string;
            }
        $this->printing($result_article);
    }

    private function printing ($article_result_array)
    {
        echo "<html><pre>";
        foreach ($article_result_array as $string)
            echo $string.".\n";
        echo "</pre></html>";
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
            $dictionary_resource_file = fopen($dictionary_file_path, 'r');
            $file_size = filesize($dictionary_file_path);
            $dictionary_text = fread($dictionary_resource_file, $file_size);
            fclose($dictionary_resource_file);

            $dictionary_text = str_replace("\n", " ", $dictionary_text);
            $new_text = wordwrap($dictionary_text, 32000, "///");
            $new_text = str_replace(" ", "|", $new_text);
            $dictionary_text_array = explode("///", $new_text);
            $this->dictionary_text_array = $dictionary_text_array;
        }
        else
            echo "Dictionary file is not readable!";
    }
}

$time_start = microtime(true);


echo "<h1>GUI</h1>";
echo "<p><label></label></p>";

$article_file_path = './files/article.txt';
$dictionary_file_path = './files/dictionary.txt';

$article    = new Article($article_file_path);
$dictionary = new Dictionary($dictionary_file_path);

$article_result_array = $article->processing($dictionary->getDictionaryTextArray());


$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Скрипт выполнялся $time секунд\n";