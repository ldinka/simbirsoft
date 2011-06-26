<p id="pages">
<?php
    for ($i=1; $i<=$pages_fd; $i++)
    {
        if ($i == $page)
            $active = " active";
        else $active = "";
        echo ' <a onclick="return paginator('.$i.');" href="'.$submit_link.'page='.$i.'&what='.$what.'&order='.$order.'" class="link01 link02'.$active.'" id="page'.$i.'">'.$i.'</a> ';
    }
?>
</p>
