</p>
<form action="<?=$submit_link?>" method="post">
    <p><label>Поиск:</label><input id="search" type="text" value="<?=strip_tags($search)?>" name="search"/></p>
    <p><label>Частота появления:</label>
       от<input id="frequency_from" type="text" value="<?=$frequency_from?>" name="frequency_from"/>
       до<input id="frequency_to" type="text" value="<?=(($frequency_to != -1)?$frequency_to:'')?>" name="frequency_to"/>
         <input onclick="return searching();" type="submit" value="Поиск"/>
    </p>
</form>
<p><img id="preloader" src="preloader.gif" alt="Идет обработка, подождите..." />&nbsp;</p>
