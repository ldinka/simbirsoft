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
    function showTemplate($nameTemplate, $arrayTemplate=array())
    {
        echo Utils::ApplyTemplate("templates/$nameTemplate.php", $arrayTemplate);
    }

    function showHeader()
    {
        echo Utils::ApplyTemplate("templates/header.php");
    }

    function showFooter()
    {
        echo Utils::ApplyTemplate("templates/footer.php");
    }

    function showError($errors)
    {
        foreach($errors as $error)
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
        echo Utils::ApplyTemplate("templates/gui.php");
    }

}
