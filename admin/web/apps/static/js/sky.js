$(".project").click(function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    var project = $(this).attr('name');
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
                for (var i=0;i<content.length;i++)
                {
                    line += '<li>';
                    line += '<span>'+content[i]+'</span><span><input name="file" type="radio" value="'+content[i]+'"></span>';
                    line += '</li>';
                }
                $(".file-list").append(line);
            }
        }
    });
});