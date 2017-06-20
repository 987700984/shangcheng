/**
 *
 *
 *   作者：***
 *
 */

//初始化操作

auationInit();

function auationInit() {
    var token = getToken();
    if (token) {
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/auationInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            data = JSON.parse(data);
            if (data['status'] == 1) {
                $('#show_tel').html(data['message']);
            } else{
                $('#show_tel').html('登录');
            }
        }).error(function (msg) {
            aa = JSON.stringify(msg);
            $.alert('请检查您的网络!或者稍候再试');
        });
    }else{
        $('#show_tel').html('登录');
    }
}

$('#changeTel').on('click',function(){
if($('#show_tel').html()=='登录') {
    window.location.href = 'login.html';
}else{
    //window.location.href = 'modifytel.html';
}
})









