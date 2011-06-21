</p>
<form action="<?=$submit_link?>" method="post">
    <p><label>Поиск:</label><input type="text" value="<?=strip_tags($search)?>" name="search"/></p>
    <p><label>Частота появления:</label>
       от<input type="text" value="<?=$frequency_from?>" name="frequency_from"/>
       до<input type="text" value="<?=(($frequency_to != -1)?$frequency_to:'')?>" name="frequency_to"/>
         <input type="submit" value="Поиск"/>
    </p>
</form>
