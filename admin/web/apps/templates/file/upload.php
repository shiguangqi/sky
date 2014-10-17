<?php require __DIR__.'/../include/header.php';?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-2 col-md-1 sidebar">
            <?php require __DIR__.'/../include/leftmenu.php';?>
        </div>
        <div class="col-sm-10 col-sm-offset-3 col-md-11 col-md-offset-1 main">
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
                            <li role="presentation" class="project" project_id="<?php echo $f['id'];?>" name="<?php echo $f['project_name'];?>"><a href="#"><?php echo $f['nick_name'];?></a></li>
                        <?php
                        }
                    }
                    ?>
                </ul>
                <div class="list-group file-list">
                </div>
                    <form id="form1" role="form" action="/file/upload_action/" method="post" enctype="multipart/form-data">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <input id="project_name" type="hidden" name="project_name" value="">
                                <input id="project_id" type="hidden" name="project_id" value="">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div style="width: 80px" class="input-group-addon">上传</div>
                                        <input style="width: 200px;" class="form-control" id="file" type="file" name="filename">
                                        <div style="display: none;float: right;height: 34px;margin: 0 5px;padding: 7px 13px;vertical-align: middle" class="alert alert-danger" role="alert"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group">
                                        <div style="width: 80px" class="input-group-addon">版本</div>
                                        <input style="width: 200px" class="form-control" name="version" value="">
                                        <div style="display: none;float: right;height: 34px;margin: 0 5px;padding: 7px 13px;vertical-align: middle" class="alert alert-danger" role="alert"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div style="width: 80px" class="input-group-addon">上传人</div>
                                        <input style="width: 200px" class="form-control" id="create_by" name="create_by" value="<?php echo $username;?>" readonly>
                                    </div>
                                </div>
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
        $("#submit").click(function(){
            var project = $("#project").val();
            if ($("#file").val() == '')
            {
                $("#file").next().html("请选择上传文件").show();
                return false;
            }
            if ($("input[name=version]").val() == '')
            {
                $("input[name=version]").next().html("请填写项目版本号").show();
                return false;
            }
            $("#form1").submit();
            return true;
        });
        $("#file").focus(function(){
            $(this).next().html('').hide(300);
        });
        $("input[name=version]").focus(function(){
            $(this).next().html('').hide(300);
        });
        initFiles();
        $(".project").click(function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            var project = $(this).attr('name');
            $("#project_name").val(project);
            $("#project_id").val($(this).attr('project_id'));
            getProjectFiles(project);
        });

        function initFiles()
        {
            var project = $(".project").first().addClass('active').attr('name');
            var project_id = $(".project").first().addClass('active').attr('project_id');
            $("#project_name").val(project);
            $("#project_id").val(project_id);
            getProjectFiles(project);
        }

        function getProjectFiles(project)
        {
            $(".file-list").empty();
            $.ajax({
                url: '/sky/getProjectFiles',
                dataType : 'json',
                data: {'name':project},
                method: 'post',
                success: function(data) {
                    if (data.status == 200)
                    {
                        var content = data.content;
                        var line = '';
                        for (var i in content)
                        {
                            line += '<a class="list-group-item files">';
                            line += content[i].filename;
                            line += '</a>';
                        }
                        $(".file-list").append(line);
                    }
                }
            });
        }

    });
</script>
</body>
</html>
