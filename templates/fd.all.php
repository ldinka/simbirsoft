<?php
    $active_all = "";
    if ($letter_id === "")
        $active_all = " active";
    echo '<p id="abc"><a onClick="return changeTableFD();" href="'.$submit_link.'" class="link01 link02'.$active_all.'" id="all">все</a> ';
?>
