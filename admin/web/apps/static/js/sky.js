initFiles();
$(".project").click(function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    var project = $(this).attr('name');
    $("input[name='project']").val(project);
    getProjectFiles(project);
    $("input[name='file']").val('');
});

function initFiles()
{
    var project = $(".project").first().addClass('active').attr('name');
    $("input[name='project']").val(project);
    getProjectFiles(project);
}

function getFilename(o)
{
    var filename = $(o).html();
    $(o).addClass('active');
    $(o).siblings().removeClass('active');
    $("input[name='file']").val(filename);
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
                var current_release = data.current_release;
                var line = '';
                for (var i in content)
                {
                    if (current_release == content[i].release)
                    {
                        line += '<a class="list-group-item files list-group-item-success" onclick="getFilename(this)">';
                    }
                    else
                    {
                        line += '<a class="list-group-item files" onclick="getFilename(this)">';
                    }

                    line += content[i].filename;
                    line += '</a>';
                }
                $(".file-list").append(line);
            }
        }
    });
}

