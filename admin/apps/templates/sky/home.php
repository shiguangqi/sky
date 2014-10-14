<?php require __DIR__.'/../include/header.php';?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-2 col-md-1 sidebar">
            <?php require __DIR__.'/../include/leftmenu.php';?>
        </div>
        <div class="col-sm-10 col-sm-offset-3 col-md-11 col-md-offset-1 main">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary">选择App</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
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
                </div>
                <input name="project" type="hidden" value="">
                <input name="version" type="hidden" value="">
                <input name="file" type="hidden" value="">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" id="choose-file">选择Files</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" id="file-list" role="menu">
                    </ul>
                </div>
                <hr/>
                <div class="panel panel-default">
                    <a style="color: #fff;width: 100%;display: inline-block;text-align: left" class="btn btn-info btn-sm" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Nodes
                    </a>
                    <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel">
                        <table id="node-list" class="table  table-hover table-condensed">
                            <thead>
                            <tr class="warning">
                                <th style='width: 30px'></th>
                                <th style='width: 30px'><span class="glyphicon glyphicon-hand-down"></span></th>
                                <th><span class='glyphicon glyphicon-user'></span>Alias</th>
                                <th><span class='glyphicon glyphicon-home'></span>Address</th>
                                <th><span class='glyphicon glyphicon-time'></span>LastHeartBit</th>
                            </tr>
                            </thead>
                            <?php
                                foreach ($nodes as $node)
                                {
                            ?>
                            <tr id='' class='danger'>
                                <td><span id='' style='display: none' class='glyphicon glyphicon-play' onclick='toggleDaemon(this);'></span></td>
                                <td></td>
                                <td><?php echo $node['alias']?></td>
                                <td id="<?php echo $node['ip']?>"><?php echo $node['ip']?></td>
                                <td class='last_time'><?php echo $node['last_time']?></td>
                            </tr>
                            <?php
                                }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <button type="button" id="install" class="btn btn-success btn-release" data-loading-text="安装中...">安装</button>
                        <button style="display: none" type="button" id="start" class="btn btn-success btn-release" data-loading-text="启动中...">启动</button>
                    </div>
                </div>
                <div class="panel panel-default">
                    <a style="color: #fff;width: 100%;display: inline-block;text-align: left" class="btn btn-info btn-sm" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Console
                    </a>
                    <div id="collapseThree" class="panel-collapse collapse in" role="tabpanel">
                        <div class="console-box panel panel-default" style="background-color: #222;color: #fff;height: 300px;overflow-y: scroll;">
                            <div class="console-bg panel-body" >
                                <div class="console">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="/static/vendor/jquery/jquery-2.0.2.min.js"></script>
<script src="/static/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="/static/vendor/jquery/jquery.json.js"></script>
<script>
    var config = <?php echo json_encode($config);?>;
    var term = {};
    term.ps1_flag = "<span class='ps1'><?php echo !empty($user['username'])?$user['username']:'sky';?>@sky# <span>|</span></span>";
</script>
<script src="/apps/static/js/php.js"></script>
<script src="/apps/static/js/sky.js"></script>
</body>
</html>
