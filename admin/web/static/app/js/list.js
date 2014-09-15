
var ListsG = {
    url: '/setting/list_data/',
    history_url : '/stats/history_data/',
    filter: {},
    stats: {},
    interface_name: {},
    interface_stats: {},
    config: {
        'green_rate': 80,
        'data_table': {
            "sPaginationType": "bootstrap_full",
            "iDisplayLength": 25,
            "aaSorting": [[0, "desc"]],
            "oLanguage": {
                "sInfo": "总计：_TOTAL_ ，当前：_START_ 到 _END_",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "前一页",
                    "sNext": "后一页",
                    "sLast": "尾页"
                }
            }
        }
    }
};

ListsG.refresh = {};

ListsG.go = function() {
    var url = '/stats/index/?';
    for (var o in ListsG.filter) {
        url += o + '=' + ListsG.filter[o] + '&';
    }
    location.href = url;
};
ListsG.getListsData = function() {
    //ListsG.refresh = getStatsData;
    var line;
    $.ajax({
        url: ListsG.url,
        dataType : 'json',
        data: ListsG.filter,
        success: function(data) {
            if (data.status == 0) {
                for (i = 0; i < data.list.length; i++) {
                    line = "<tr height='32'>";
                    //接口id
                    line += '<td>' + data.list[i]['id'] + '</td>';
                    line += '<td>' + data.list[i]['name'] + '</td>';
                    line += '<td>' + data.list[i]['alias'] + '</td>';
                    line += '<td>' + data.list[i]['succ_hold'] + '</td>';
                    line += '<td>' + data.list[i]['wave_hold'] + '</td>';
                    line += '<td>' + data.list[i]['owner_name'] + '</td>';
                    line += '<td>' + data.list[i]['addtime'] + '</td>';
                    line += '<td>';
                        line += '<a href="/setting/add_interface/?id=' +data.list[i]['id']+ '" class="btn btn-info btn-xs">修改</a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    line += '<a value="' +data.list[i]['id']+ '" onclick="deleteItem(this)" href="javascript:void(0)" class="btn btn-danger btn-xs">删除</a>';
                    line += '</td>';
                    line += "</tr>";
                    $('#data_table_body').append(line);
                }
            }
            ListsG.dataTable = $('#data_table_stats').dataTable(ListsG.config.data_table);
        }
    });
}

function deleteItem(obj) {
    var id = $(obj).attr('value');
    $.ajax({
        url: '/setting/delete_interface/?id='+id,
        dataType : 'json',
        data: ListsG.filter,
        success: function(data) {
            if (data.status == 0) {
                $(obj).parent().parent().remove();
                var tip = '<div class="alert alert-success fade in">'+
                    '<button class="close" data-dismiss="alert">×</button>'+
                    '<i class="fa-fw fa fa-check"></i>'+
                    '<strong>'+data.msg+id+'</strong>'+
                    '</div>';
            } else {
                //删除失败
                var tip = '<div class="alert alert-danger fade in">'+
                    '<button class="close" data-dismiss="alert">×</button>'+
                    '<i class="fa-fw fa fa-check"></i>'+
                    '<strong>'+data.msg+id+'</strong>'+
                    '</div>';

            }
            $("#delete_tip").append(tip);
        }
    });
}