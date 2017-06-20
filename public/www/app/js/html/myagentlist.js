/**
 * Created by Administrator on 2016/10/12 0012.
 */
/**
 * Created by Administrator on 2016/10/12 0012.
 */



//执行初始化
myagentlistInit();

//进入页面时初始化数据等
function myagentlistInit(){

    $(function () {
        FastClick.attach(document.body);
    });
    jQuery.support.cors = true;
    var httpHost = getHttpHost();

    $.post(httpHost + '/index/Initdata/myagentlistInit.html', {
        'token': getToken(),
        'fromwhere': getFromwhereType()
    }, function (data) {
        if (data == 0) {
            $('#backButton').insertBefore( '  <p>暂时没有推荐用户！</p>')
        } else {
            data = JSON.parse(data);
            dataToForm(data);
        }
    }).error(function (msg) {
        aa = JSON.stringify(msg);
        $.alert('请检查您的网络!或者稍候再试');
    });

}


//将数据库中的字段渲染到页面里
function dataToForm(data){

    var html = '';
    for(var i in data){
        console.log(i);console.log(data[i]);
        html +=  ' <div class="weui_cell"> <div class="weui_cell_bd weui_cell_primary "> <p>' + data[i].real_name+ '</p></div> <div class="weui_cell_ft">'+data[i].phone +'</div> </div>';
    }
        $('#backButton').before( html);
}






