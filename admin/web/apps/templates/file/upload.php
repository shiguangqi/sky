
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SKY</title>
    <link href="/static/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/apps/static/css/style.css" rel="stylesheet">
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">SKY监控系统</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <?php require __DIR__.'/../include/leftmenu.php';?>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="page-header">
                <h3><small>上传文件</small></h3>
            </div>
            <div class="well well-lg">
                <ul class="nav nav-pills" role="tablist" style="padding-bottom: 10px">
                    <?php
                    if (!empty($projects))
                    {
                        foreach ($projects as $f)
                        {
                            ?>
                            <li role="presentation" class="project" name="<?php echo $f['project_name'];?>"><a href="#"><?php echo $f['nick_name'];?></a></li>
                        <?php
                        }
                    }
                    ?>
                </ul>
                    <form id="form1" role="form" action="/file/upload_action/" method="post" enctype="multipart/form-data">
                        <div class="well well-sm">
                            <div class="form-group">
                                <input id="project" type="hidden" name="project" value="">
                                <input id="file" type="file" name="filename">
                                <p class="help-block">选择将要上传的文件</p>
                            </div>
                        </div>
                        <button id="submit" type="submit" class="btn btn-primary">Submit</button>
                    </form>
            </div>
        </div>
    </div>
</div>

<script src="/static/vendor/jquery/jquery-2.0.2.min.js"></script>
<script src="/static/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="/static/vendor/jquery/jquery.json.js"></script>
<script src="/apps/static/js/php.js"></script>
<script>
    $(document).ready(function () {
        $(".project").click(function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $("#project").val($(this).attr('name'));
            $(".file-list").empty();
        });

        $("#submit").click(function(){
            var project = $("#project").val();
            if (project == '')
            {
                alert("请选择项目");
                return false;
            }
            if ($("#file").val() == '')
            {
                alert("请选择文件");
                return false;
            }
            $("#form1").submit();
        });
    });
</script>
</body>
</html>
