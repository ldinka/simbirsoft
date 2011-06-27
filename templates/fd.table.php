<table>
    <thead>
    <tr>
        <th>
            <p>Слово
                <a onclick="return sorting('asc', 'word');" href="<?=$submit_link?>page=<?=$page?>&what=word&order=asc" class="sorting" id="word_asc">прямо</a>
                <a onclick="return sorting('desc', 'word');" href="<?=$submit_link?>page=<?=$page?>&what=word&order=desc" class="sorting" id="word_desc">обратно</a>
            </p>
        </th>
        <th>
            <p>Частота
                <a onclick="return sorting('asc', 'frequency');" href="<?=$submit_link?>page=<?=$page?>&what=frequency&order=asc" class="sorting" id="frequency_asc">прямо</a>
                <a onclick="return sorting('desc', 'frequency');" href="<?=$submit_link?>page=<?=$page?>&what=frequency&order=desc" class="sorting" id="frequency_desc">обратно</a>
            </p>
        </th>
    </tr>
    </thead>
    <tbody>
<?foreach ($result_arr as $row):?>
    <tr><td><p><label><?=$row["word"]?></label></p><td><p><?=$row["frequency"]?></p></td></tr>
<?endforeach;?>
    </tbody>
</table>
