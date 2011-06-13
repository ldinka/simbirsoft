<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 05.06.11
 * Time: 23:08
 * To change this template use File | Settings | File Templates.
 */
 
class View
{
    function showHeader()
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
                .link02 {
                    color: #bbb;
                    display: inline-block;
                    font-weight: bold;
                    margin: 5px;
                    text-transform: uppercase;
                }
                .link01.link02 {
                    color: #00438F;
                    margin: 5px 3px;
                }
                .error {
                    background: #f00;
                    color: #fff;
                    font-weight: bold;
                }
                #preloader {
                    display: none;
                }
            </style>
            <script type="text/javascript">
                function viewPreloader()
                {
                    document.getElementById("submit").style.display = "none";
                    document.getElementById("preloader").style.display = "block";
                }
            </script>
            </head>
            <body>';
    }

    function showFooter()
    {
        echo '</body>
            </html>';
    }

    function showError($err)
    {
        foreach($err as $error)
        {
            echo '<p><span class="error">'.$error.'</span></p>';
        }
    }

    function showPages($pages)
    {
        echo $pages;
    }

    function showGUI()
    {
        echo  '<h1>Graphical User Interface</h1>
        <form action="index.php" method="post" enctype="multipart/form-data">
        <p><label>Загрузить файл статьи (не более 2Мб)</label><input type="file" name="article"/></p>
        <p><label>Загрузить файл словаря (не более 2Мб)</label><input type="file" name="dictionary"/></p>
        <p><label>Количество выводимых строк (10-100000)</label><input type="text" name="number_of_strings" value="'.(isset($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):100).'"/></p>
        <input type="hidden" name="go" value="1"/>
        <p><input type="submit" id="submit" onClick="viewPreloader()"/>
           <img id="preloader" src="preloader.gif" alt="Идет обработка, подождите..." /></p>
        </form>';
    }

}
