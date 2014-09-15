/**
 * Created by htf on 14-4-29.
 */
var LogsG = {
    url: '/logs/data/',
    history_url : '/stats/history_data/',
    filter: {
        hour_start: 0,
        hour_end: 23
    },
    stats: {},
    interface_name: {},
    interface_stats: {},
    config: {
        'green_rate': 80,
        'data_table': {
            "bAutoWidth": true,
            "bDestroy": true,
            "bServerSide": true,
            "sPaginationType": "bootstrap_full",
            "iDisplayLength": 25,
            "oLanguage": {
                "aaSorting": [[0, "asc"]],
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

var history_chart_option = {
    calculable : true,
    xAxis : [
        {
            type : 'category',
            boundaryGap : false,
            data : []
        }
    ],
    yAxis : [
        {
            type : 'value',
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'最高气温',
            type:'line',
            itemStyle: {
                borderWidth : 1,
                borderRadius : 2,
                normal: {
                    lineStyle: {
                        shadowColor : 'rgba(0,0,0,0.4)',
                        shadowBlur: 5,
                        shadowOffsetX: 3,
                        shadowOffsetY: 3
                    }
                }
            },
            data:[]
        },
        {
            name:'最低气温',
            type:'line',
            itemStyle: {
                borderWidth : 1,
                borderRadius : 2,
                normal: {
                    lineStyle: {
                        shadowColor : 'rgba(0,0,0,0.4)',
                        shadowBlur: 5,
                        shadowOffsetX: 3,
                        shadowOffsetY: 3
                    }
                }
            },
            data:[1, -2, 2, 5, 3, 2, 0]
        }
    ]
};

LogsG.filterByHour = function () {
    LogsG.filter.hour_start = parseInt($('#filter_hour_s').val(), 10);
    LogsG.filter.hour_end = parseInt($('#filter_hour_e').val(), 10);
    LogsG.refresh(LogsG.filter.interface_id);
};

LogsG.refresh = {};

function paserHistoryData(data) {
    var ret = {};
    for(var i=0; i< data.length; i++) {
        ret[data[i].time_key] = data[i];
    }
    return ret;
}

LogsG.showHistoryData = function () {
    var filter =  LogsG.filter;
    filter.date_start = $('#history_date_start').val();
    filter.date_end = $('#history_date_end').val();
    LogsG.refresh = LogsG.showHistoryData;

    $.ajax({
        url: LogsG.history_url,
        dataType : 'json',
        data: filter,
        success: function(data) {
            var data1 = paserHistoryData(data.data1.stats);
            var data2 = paserHistoryData(data.data2.stats);

            require(['echarts', 'echarts/chart/bar'], function(ec) {
                history_chart_option.xAxis[0].data = [];
                history_chart_option.series[0].data = [];
                history_chart_option.series[1].data = [];

                var time_start = LogsG.filter.hour_start * 12;
                var time_end = (LogsG.filter.hour_end + 1) * 12;
                var myChart1 = ec.init(document.getElementById('history_chart1'));
                $('#history_table').html('');
                for(var i = time_start; i< time_end; i++) {
                    history_chart_option.xAxis[0].data.push(getTimerStr(i));
                    if (data1[i]) {
                        history_chart_option.series[0].data.push(data1[i].total_count);
                    } else {
                        history_chart_option.series[0].data.push(0);
                    }

                    if (data2[i]) {
                        history_chart_option.series[1].data.push(data2[i].total_count);
                    } else {
                        history_chart_option.series[1].data.push(0);
                    }
                    var _data1 = {}, _data2 = {};
                    if (data1[i]) {
                        _data1 = data1[i];
                    }
                    if (data2[i]) {
                        _data2 = data2[i];
                    }
//                    if (_data1[i])
                    LogsG.appendToHistoryTable(_data1, _data2);
                }
                myChart1.setOption(history_chart_option);
            });
        }
    });
};

LogsG.go = function() {
    var url = '/logs/index/?';
    for (var o in LogsG.filter) {
        url += o + '=' + LogsG.filter[o] + '&';
    }
    location.href = url;
};

LogsG.appendToHistoryTable = function (_data1, _data2) {
    var tr_color; //green, normal
    if (!_data1['total_count'] && !_data2['total_count']) {
        return;
    } else {
        //console.dir(_data1);
        //console.dir(_data2);
    }
    var line;
    var fail_rate, avg_fail_time, avg_time, td_color;
    line = "<tr height='32'>";
    //时间
    var time_key = _data1.time_key ? _data1.time_key : _data2.time_key;
    var date_key;
    for(var i = 0; i < 2; i++) {
        if (i == 0) {
            d = _data1;
            date_key = LogsG.filter.date_start;
            line += '<td width="100">' + LogsG.filter.date_start + ' '+ getTimerStr(time_key) + '</td>';
        } else {
            d = _data2;
            date_key = LogsG.filter.date_end;
            line += '<td width="100">' + LogsG.filter.date_end + ' '+ getTimerStr(time_key) + '</td>';
        }
        if (!d['total_count']) {
            j = 1;
            line += '<td width="100"> -- </td>';
            line += '<td width="100"> -- </td>';
            line += '<td width="100"> -- </td>';
            line += '<td width="100"> -- </td>';
            line += '<td width="100"> -- </td>';
        } else {
//            console.dir(d);
            fail_rate = round((d['total_count'] - d['fail_count']) / d['total_count'] * 100, 2);
            //调用次数
            line += '<td width="100">' + d['total_count'] + '</td>';
            if (fail_rate >= LogsG.config.green_rate) {
                td_color = 'green';
            } else {
                td_color = 'red';
            }
            //失败次数
            line += '<td width="100"><a href="javascript: LogsG.openFailPage('+LogsG.filter.interface_id+',' +
                '\''+time_key+'\', \''+ date_key+'\')" ' +
                'style="color: red; ">' + d['fail_count'] + '</td>';
            //成功率
            line += '<td width="100" style="color: '+td_color+'">' + fail_rate + '%</td>';
            //平均响应事件
            avg_time = round(d['total_time'] / d['total_count'], 2);
            line += '<td width="100">' + avg_time + 'ms </td>';
            //失败响应时间
            if (d['total_fail_time'] > 0) {
                avg_fail_time = round(d['total_fail_time'] / d['fail_count'], 2);
                line += '<td width="100">' + avg_fail_time + 'ms </td>';
            } else {
                line += '<td width="100"> -- </td>';
            }
        }
    }
    line += "</tr>";
    $('#history_table').append(line);
    i = 0;
    $('#history_table tr').each(function(e, o){
        if ((i++%2)==1) {
            $(o).attr('style', "background-color: #efefef;");
        }
    });
};

LogsG.appendToTable = function (interface_id, _data, option) {
    var line;
    var tr_color; //green, normal
    if (!_data['total_count']) {
        return;
    }
    _data.interface_id = interface_id;
    var stats_str = parseStatsData(_data);
    if (_data.fail_rate < LogsG.config.green_rate) {
        tr_color = '#FFDFDF';
    } else {
        tr_color = '#DFFFDF';
    }
    line = "<tr height='32' style='background-color: "+tr_color+"' width='100%'>";
    //接口名称
    line += '<td>' + LogsG.interface_name[interface_id] + '</td>';
    //日期
    //+ data.date + ' '
    //时间
    line += '<td>' + _data.time_str + '</td>';
    line += stats_str;
    line += '<td>';
    if (!option.no_detail) {
        line += '<a href="javascript: LogsG.showDetail(' + interface_id + ')">查看明细</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
        line += '<a href="/stats/history/?module_id='+_data['module_id']+'&interface_id=' + interface_id + '">历史数据对比</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
        line += '<a href="/stats/client/?module_id='+_data['module_id']+'&interface_id=' + interface_id + '">主调明细</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
        line += '<a href="/stats/server/?module_id='+_data['module_id']+'&interface_id=' + interface_id + '">被调明细</a>';
    }
    line += '</td>';

    line += "</tr>";
    $('#data_table_body').append(line);
};

function parseStatsData(_data) {
    var line = '';
    var fail_rate, avg_fail_time, avg_time, td_color;
    // 成功率
    fail_rate = round((_data['total_count'] - _data['fail_count']) / _data['total_count'] * 100, 2);
    //调用次数
    line += '<td>' + _data['total_count'] + '</td>';
    if (fail_rate >= LogsG.config.green_rate) {
        td_color = 'green';
    } else {
        td_color = 'red';
    }
    _data['fail_rate'] = fail_rate;
    //失败次数
    if (_data['fail_count'] > 0) {
        line += '<td><a href="javascript: LogsG.openFailPage('+_data.interface_id+')" ' +
            'style="color: red; ">' + _data['fail_count'] + '</td>';
    } else {
        line += '<td> 0 </td>';
    }
    //成功率
    line += '<td style="color: '+td_color+'">' + fail_rate + '%</td>';
    //平均响应事件
    avg_time = round(_data['total_time'] / _data['total_count'], 2);
    line += '<td>' + avg_time + 'ms </td>';

    //失败响应时间
    if (_data['fail_count'] > 0) {
        avg_fail_time = round(_data['total_fail_time'] / _data['fail_count'], 2);
        line += '<td>' + avg_fail_time + 'ms </td>';
    } else {
        line += '<td> -- </td>';
    }
    return line;
}

LogsG.appendToTable2 = function (ip, _data, param) {
    var line;
    var tr_color; //green, normal
    //console.dir(_data);
    //console.dir(interface_id);
    if (!_data['total_count']) {
        return;
    }
    var stats_str = parseStatsData(_data);
    line = "<tr height='32'>";
    //机器IP
    line += '<td>' + ip + '</td>';
    //调用比例
    line += '<td>' + round((_data['total_count'] / param['total_count'])*100, 2)  + '% </td>';
    //失败比例
    if (param['fail_count']) {
        line += '<td style="color: red">' + round((_data['fail_count'] / param['fail_count'])*100, 2)  + '% </td>';
    } else {
        line += '<td> -- </td>';
    }
    //日期
    line += stats_str;
    line += "</tr>";
    $('#data_table_body').append(line);
};

LogsG.showDetail = function (interface_id) {
    LogsG.filter.interface_id = interface_id;
    LogsG.refresh = LogsG.showDetail;
    $('#interface_id').val(interface_id+': '+LogsG.interface_name[interface_id]);
    var o, hour, i;
    $('#data_table_body').html('');
    LogsG.interface_stats[interface_id].sort(function(a, b){
        return a.time_key - b.time_key;
    });
    //将所有接口的统计数据进行汇总
    for (i = 0; i < LogsG.interface_stats[interface_id].length; i++) {
        o = LogsG.interface_stats[interface_id][i];
        o.time_str = getTimerStr(o.time_key);
        hour = parseInt(o.time_str.split(':')[0], 10);
        if (hour < LogsG.filter.hour_start || hour > LogsG.filter.hour_end) {
            continue;
        }
        LogsG.appendToTable(interface_id, o, {no_detail: true});
    }
}

function fillZero4Time(s) {
    if (s < 10) {
        return '0' + s;
    } else {
        return s;
    }
}

function getTimerStr(time_key) {
    var _h = time_key / 12.0;
    var h = parseInt(_h, 10);
    var _m = round((((_h - h) * 60)/5)*5);
    return fillZero4Time(h) + ':'+ fillZero4Time(_m);
}

LogsG.openFailPage = function (interface_id, time_key, date_key) {
    var url = '/stats/fail/?';
    url += 'module_id=' + LogsG.filter.module_id;
    if (interface_id) {
        url += '&interface_id=' + interface_id;
    } else {
        url += '&interface_id=' + LogsG.filter.interface_id;
    }
    if (date_key) {
        url += '&date_key=' + date_key;
    } else {
        url += '&date_key=' + LogsG.filter.date_key;
    }
    if (time_key) {
        url += '&time_key=' + time_key;
    }
    location.href = url;
};

function getLogsData() {
    LogsG.refresh = getStatsData;
    $.ajax({
        url: LogsG.url,
        dataType : 'json',
        data: LogsG.filter,
        beforeSend: function() {
            $('#data_table_body').html('');
        },
        success: function(data) {
            if (data.status == 200) {
                for (i = 0; i < data.content.length; i++) {
                    line = "<tr height='32'>";
                    line += '<td>' + data.content[i]['module_id'] + '</td>';
                    line += '<td>' + data.content[i]['module_name'] + '</td>';
                    line += '<td>' + data.content[i]['interface_id'] + '</td>';
                    line += '<td>' + data.content[i]['interface_name'] + '</td>';

                    line += '<td>';
                    line += '<a href="/logs/detail_list/?m_id=' +data.content[i]['module_id']+ '&f_id=' +data.content[i]['interface_id']+ '" class="btn btn-success btn-xs">查看明细</a>&nbsp;&nbsp;&nbsp;&nbsp;';
//                    line += '<a value="' +data.content[i]['id']+ '" onclick="deleteItem(this)" href="javascript:void(0)" class="btn btn-danger btn-xs">删除</a>';
                    line += '</td>';
                    line += "</tr>";
                    $('#data_table_body').append(line);
                }
            }
            $('#data_table_stats').DataTable().clear().destroy();
            ListsG.dataTable = $('#data_table_stats').dataTable(ListsG.config.data_table);
        }
    });
}

LogsG.showDetailStats = function() {
    LogsG.refresh = LogsG.showDetailStats;
    $.ajax({
        url: LogsG.url,
        dataType : 'json',
        data: LogsG.filter,
        success: function(data) {
            var ip = '';
            var stats = {};
            var total_count = 0;
            var fail_count = 0;

            //将interface组成一个map
            for (var i = 0; i < data.length; i++) {
                ip = data[i].ip;
                total_count += parseInt(data[i]['total_count'], 10);
                fail_count +=  parseInt(data[i]['fail_count'], 10);

                if (!stats[ip]) {
                    stats[ip] = {
                        'ip' : ip,
                        'interface_id': LogsG.filter.interface_id,
                        'total_count': 0,
                        'fail_count': 0,
                        'total_fail_time': 0.0,
                        'total_time': 0.0
                    }
                }
            }
            //将所有接口的统计数据进行汇总
            for (i = 0; i < data.length; i++) {
                ip = data[i]['ip'];
                stats[ip]['total_count'] += parseInt(data[i]['total_count']);
                stats[ip]['fail_count'] += parseInt(data[i]['fail_count'], 10);
                stats[ip]['total_fail_time'] += parseFloat(data[i]['total_fail_time']);
                stats[ip]['total_time'] += parseFloat(data[i]['total_time']);
            }
            for (ip in stats) {
                LogsG.appendToTable2(ip, stats[ip], {'total_count' : total_count, 'fail_count' : fail_count});
            }
            LogsG.dataTable = $('#data_table_stats').dataTable(LogsG.config.data_table);
        }
    });
}/**
 * Created by shiguangqi on 14-8-12.
 */
