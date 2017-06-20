/**
 * Created by Administrator on 2016/10/12 0012.
 */



//执行初始化
loanInit();
var process = 0;
//进入页面时初始化数据等
function loanInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Index/getProcessLoan.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if(data==0){
                goEnroll();
            }
                data = JSON.parse(data);
                 process = getProcess(data[0]);
        }).error(function (msg) {
            aa = JSON.stringify(msg);
            //$.alert('请检查您的网络!或者稍候再试');
            $.alert('请检查您的网络或稍候再试!');
        });
    }else{
        goEnroll();
    }
}


$('#iWillBorrow').on('click',function(){
        var money = $("[name='radio1']:checked").parent().prev().children().html();
        if(process!=100){
            $.alert('您的资料还未全部通过验证,请耐心等待，或者去<a style="text-decoration: underline" href="personaldata.html">我的中心</a>补全资料！');
            return;
        }
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
    $.post(httpHost+'/index/index/getNeed.html',{'require':money, 'token': getToken(), 'fromwhere': getFromwhereType()},function(data){
        postSuccess(data);
    }).error(function(msg) {
        aa = JSON.stringify(msg);
        $.alert('请检查您的网络或稍候再试!');
    });
}
)


$('#to_shop').on('click',function(){
    var to_shop = $("#my_value").val();
    if(process!=100){
            $.alert('您的资料还未全部通过验证,请耐心等待，或者去<a style="text-decoration: underline" href="personaldata.html">我的中心</a>补全资料！');
            return;
        }
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost+'/index/Index/getNeed.html',{'require':to_shop, 'token': getToken(), 'fromwhere': getFromwhereType()},function(data){
postSuccess(data);
        }).error(function(msg) {
            aa = JSON.stringify(msg);
            $.alert('请检查您的网络或稍候再试!');
        });
    }
)


//提交表单回调成功的操作
function postSuccess(data,myForm){
    if(data==1){
        $.alert('您的需求提交成功，稍候会有工作人员联系您，请耐心等待！',function(){
            window.location.href='index.html';

        });

    }else if(data==0){
      goEnroll();
    }else if(data==2){
        $.alert('您的需求已经提交过，请不要重得提交!',function(){
            window.location.href='index.html';

        });

    }else{
        $.alert(data);
    }
}

