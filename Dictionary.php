<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 03.06.11
 * Time: 4:45
 * To change this template use File | Settings | File Templates.
 */

class Dictionary
{
    private $dictionary_text_array;

    /**
     * Return dictionary array
     * @return array
     *
     * */
    public function getDictionaryTextArray()
    {
        return $this->dictionary_text_array;
    }

    /**
     * constructor
     * @param path of dictionary file
     * */
    function __construct($dictionary_file_path)
    {
        if (file_exists($dictionary_file_path) && is_readable($dictionary_file_path))
        {
            $file_size = filesize($dictionary_file_path);

            if($file_size==0)
                throw new Exception("Dictionary filesize must be more than zero bytes");

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
                if (preg_match("~[^\p{L}_0-9\s+]~ui", $word))
                {
                    $word       = preg_quote($word);
                    $word       = preg_replace("~[^\p{L}_0-9\s+]~ui", " ", $word);
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
            $dictionary_text  = implode(" ", $dictionary_array);

            $new_text = wordwrap($dictionary_text, 32000, "///");
            $new_text = str_replace(" ", "|", $new_text);

            $dictionary_text_array = explode("///", $new_text);
            $dictionary_text_array = array_unique($dictionary_text_array);

            $this->dictionary_text_array = $dictionary_text_array;
        }
        else
            throw new Exception("Dictionary file is not readable!");
    }
}