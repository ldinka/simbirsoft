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
                    border: 1px solid #999;
                    border-bottom-color: #fff;
                    color: #00438F;
                    display: inline;
                    font-size: 16px;
                    padding: 15px 20px 0;
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
                #header {
                    border-bottom: 1px solid #999;
                    margin: 20px 0;
                    padding: 5px 15px 0;
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
                .link03 {
                    background: #eee;
                    border: 1px solid #999;
                    color: #537196;
                    font-size: 16px;
                    font-weight: bold;
                    padding: 10px 20px 0;
                    text-decoration: none;
                }
                .link03:hover {
                    padding-top: 15px;
                }
                .link01.link02.active {
                    background: #00438F;
                    color: #fff;
                    text-decoration: none;
                }
                .error {
                    background: #f00;
                    color: #fff;
                    font-weight: bold;
                }
                #preloader {
                    display: none;
                }
                .display-none {
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
        echo  '<div id="header">
        <h1>Graphical User Interface</h1>
        <a href="index.php?module=fd" class="link03">Frequency dictionary</a>
        </div>
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
