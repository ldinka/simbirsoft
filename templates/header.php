<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
    function viewPreloader()
    {
        document.getElementById("submit").style.display = "none";
        document.getElementById("preloader").style.display = "block";
    }

    function showPreloader()
    {
        $("#preloader").show();
    }
    function hidePreloader()
    {
        $("#preloader").hide();
    }
</script>
<script type="text/javascript" src="/scripts/jquery-1.6.1.min.js"></script>
<script type="text/javascript">
function changeTableFD (letter_id)
{
    $("#search").val("");
    $("#frequency_from").val("0");
    $("#frequency_to").val("");
    $(".sorting").removeClass("active");
    if (letter_id == null)
    {
        letter = "#all";
        letter_id = "all";
    }
    else
    {
        letter = "#letter" + letter_id;
    }
    $("#abc .active").removeClass("active");
    $(letter).addClass("active");
    showPreloader();
    $.ajax({
        type : "GET",
        dataType: "json",
        url  : "index.php",
        data : "ajax=yes&filter=letter&letter="+letter_id,
        success: function(msg){
            hidePreloader();
            result_table = "";
            cnt = msg.words.length;
            $.each(msg.words, function(i, val){
               result_table += "<tr><td><p><label>" + msg.words[i].name + "</label></p><td><p>" + msg.words[i].count + "</p></td></tr>";
            });
            $("tbody").html(result_table);
            result_pages = "";
            if (msg.pages == 1)
            {
                $("#pages").html("");
            }
            else
            {
                for (i=1; i<=msg.pages; i++)
                {
                    result_pages += ' <a onclick="return paginator(' + i + ');" href="' + msg.submit_link + 'page=' + i + '" class="link01 link02" id="page' + i + '">' + i + '</a> ';
                }
                $("#pages").html(result_pages);
                $("#page1").addClass("active");
            }
        },
        error: function(err)
        {
            hidePreloader();
            console.log(err);
        }
    });
    return false;
}

function sorting (order, what)
{
    if ($("#abc .active").html() != null)
    {
        letter_id = $("#abc .active").attr("id").replace("letter", "");
    }
    if($("#pages .active").html() != null)
        page = $("#pages .active").attr("id").replace("page", "");
    else
        page = "";
    $(".sorting").removeClass("active");
    $("#"+what+"_"+order).addClass("active");
    search = $("#search").val();
    frequency_from = $("#frequency_from").val();
    frequency_to = $("#frequency_to").val();
    if (search || frequency_from > 0 || (frequency_to != "" && frequency_to >= 0))
    {
        data_request = "ajax=yes&filter=searching&search="+search+"&frequency_from="+frequency_from+"&frequency_to="+frequency_to+"&page="+page+"&what="+what+"&order="+order;
    }
    else
    {
        data_request = "ajax=yes&filter=sorting&letter="+letter_id+"&what="+what+"&page="+page+"&order="+order;
    }
    showPreloader();
    $.ajax({
        type : "GET",
        dataType: "json",
        url  : "index.php",
        data : data_request,
        success: function(msg){
            hidePreloader();
            result_table = "";
            cnt = msg.words.length;
            $.each(msg.words, function(i, val){
               result_table += "<tr><td><p><label>" + msg.words[i].name + "</label></p><td><p>" + msg.words[i].count + "</p></td></tr>";
            });
            $("tbody").html(result_table);
        },
        error: function(err)
        {
            hidePreloader();
            console.log(err);
        }
    });
    return false;
}

function paginator (page)
{
    if ($("#abc .active").html() != null)
    {
        letter_id = $("#abc .active").attr("id").replace("letter", "");
    }
    if($("#pages .active").html() != null)
        $("#pages .active").removeClass("active");

    $("#page"+page).addClass("active");
    if ($(".sorting.active").html() == "прямо" || $(".sorting.active").html() == "обратно")
    {
        what = $(".sorting.active").attr("id").replace(/asc|desc|_/gi, "");
        order = $(".sorting.active").attr("id").replace(/word|frequency|_/gi, "");
    }
    else
    {
        what = "";
        order = "";
    }
    search = $("#search").val();
    frequency_from = $("#frequency_from").val();
    frequency_to = $("#frequency_to").val();
    if (search || frequency_from > 0 || (frequency_to != null && frequency_to >= 0))
    {
        data_request = "ajax=yes&filter=searching&search="+search+"&frequency_from="+frequency_from+"&frequency_to="+frequency_to+"&page="+page+"&what="+what+"&order="+order;
    }
    else
    {
        data_request = "ajax=yes&filter=paginator&letter="+letter_id+"&what="+what+"&page="+page+"&order="+order;
    }
    showPreloader();
    $.ajax({
        type : "GET",
        dataType: "json",
        url  : "index.php",
        data : data_request,
        success: function(msg){
            hidePreloader();
            result_table = "";
            cnt = msg.words.length;
            $.each(msg.words, function(i, val){
               result_table += "<tr><td><p><label>" + msg.words[i].name + "</label></p><td><p>" + msg.words[i].count + "</p></td></tr>";
            });
            $("tbody").html(result_table);
        },
        error: function(err)
        {
            hidePreloader();
            console.log(err);
        }
    });
    return false;
}

function searching ()
{
    $(".sorting").removeClass("active");
    $("#abc .active").removeClass("active");
    search = $("#search").val();
    $("#search").val(search);
    frequency_from = $("#frequency_from").val();
    $("#frequency_from").val(frequency_from);
    frequency_to = $("#frequency_to").val();
    $("#frequency_to").val(frequency_to);
    showPreloader();
    $.ajax({
        type : "GET",
        dataType: "json",
        url  : "index.php",
        data : "ajax=yes&filter=searching&search="+search+"&frequency_from="+frequency_from+"&frequency_to="+frequency_to,
        success: function(msg){
            hidePreloader();
            result_table = "";
            if (!msg.error)
            {
                cnt = msg.words.length;
                $.each(msg.words, function(i, val){
                   result_table += "<tr><td><p><label>" + msg.words[i].name + "</label></p><td><p>" + msg.words[i].count + "</p></td></tr>";
                });
                $("tbody").html(result_table);
                result_pages = "";
                if (msg.pages == 1)
                {
                    $("#pages").html("");
                }
                else
                {
                    for (i=1; i<=msg.pages; i++)
                    {
                        result_pages += ' <a onclick="return paginator(' + i + ');" href="' + msg.submit_link + 'page=' + i + '" class="link01 link02" id="page' + i + '">' + i + '</a> ';
                    }
                    $("#pages").html(result_pages);
                    $("#page1").addClass("active");
                }
            }
            else
            {
                result_table += "<tr colspan='2'><td><p>" + msg.error + "</p></td></tr>";
                $("tbody").html(result_table);
                $("#pages").html("");
            }
        },
        error: function(err)
        {
            hidePreloader();
            console.log(err);
        }
    });
    return false;
}
</script>
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
</head>
<body>