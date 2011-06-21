<?php
if (!empty($temp_array))
{
    if ($letter_id === $key)
        $active = " active";
    else $active = "";
        echo ' <a href="'.$submit_link.'letter='.$key.'" class="link01 link02'.$active.'">'.$letter.'</a> ';
}
else
    echo ' <span class="link02">'.$letter.'</span> ';
?>
