<div id="header">
<h1>Graphical User Interface</h1>
<a href="index.php?module=fd" class="link03">Frequency dictionary</a>
</div>
<form action="index.php" method="post" enctype="multipart/form-data">
<p><label>Загрузить файл статьи (не более 2Мб)</label><input type="file" name="article"/></p>
<p><label>Загрузить файл словаря (не более 2Мб)</label><input type="file" name="dictionary"/></p>
<p><label>Количество выводимых строк (10-100000)</label><input type="text" name="number_of_strings" value="<?=(isset($_REQUEST['number_of_strings'])?intval($_REQUEST['number_of_strings']):100)?>"/></p>
<input type="hidden" name="go" value="1"/>
<p><input type="submit" id="submit" onClick="viewPreloader()"/>
   <img id="preloader" src="preloader.gif" alt="Идет обработка, подождите..." /></p>
</form>