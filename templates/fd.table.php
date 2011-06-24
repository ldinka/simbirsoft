<table>
    <thead>
    <tr>
        <th>
            <p>Слово
                <a href="<?=$submit_link?>page=<?=$page?>&what=word&order=asc">прямо</a>
                <a href="<?=$submit_link?>page=<?=$page?>&what=word&order=desc">обратно</a>
            </p>
        </th>
        <th>
            <p>Частота
                <a href="<?=$submit_link?>page=<?=$page?>&what=frequency&order=asc">прямо</a>
                <a href="<?=$submit_link?>page=<?=$page?>&what=frequency&order=desc">обратно</a>
            </p>
        </th>
    </tr>
    </thead>
    <tbody>
<?foreach ($result_arr as $row):?>
    <tr><td><p><label><?=$row["word"]?></label></p><td><p><?=$row["frequency"]?></p></td></tr>
<?endforeach;?>
    <t/body>
</table>
