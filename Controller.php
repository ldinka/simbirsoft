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
        $time_start = microtime(true);
        $this->view->showTemplate("header");
        try
        {
            $this->run();
        }
        catch (Exception $e)
        {
            $this->view->showTemplate("error", array("errors"=>$e->getMessage()));
        }
        $time_end = microtime(true);
        $time     = $time_end - $time_start;
        $this->view->showTemplate("time", array("time"=>$time));
        $this->view->showTemplate("footer");
    }

    private function run()
    {
        $module = isset($_REQUEST["module"])?$_REQUEST["module"]:"";
        switch ($module)
        {
            case "fd":  #frequency dictionary page
                $this->fd->init();
                break;
            case "gui":  #graphical user interface page
            default:
                //$this->view->showGUI();
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
    }
}
