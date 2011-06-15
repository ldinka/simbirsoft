<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 03.06.11
 * Time: 4:46
 * To change this template use File | Settings | File Templates.
 */
 
class Utils
{
    static function f_print_r ($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    static function f_flush ($string)
    {
        ob_implicit_flush(1);
        echo $string;
        ob_implicit_flush(0);
    }

    private static function FileRead($filename){
	$handle = fopen($filename, "r");
	$content = @fread($handle, filesize($filename));
	fclose($handle);
	return $content;
}

    private static function ApplyTemplateData($template, $params = Array()){
        foreach($params as $k => $p){
            if(!is_int($k)){
                $cmd = '$'.$k.' = $p;';
                eval($cmd);
            }
        }
        ob_start();
        eval('?>'.$template.'<?');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function ApplyTemplate($tpl_name, $params = Array()){
        return Utils::ApplyTemplateData(Utils::FileRead($tpl_name), $params);
    }
}
