/**
 * Created by Administrator on 2016/10/12 0012.
 */



torepayInit();
var dingdan = '';
//进入页面时初始化数据等
function torepayInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/toRepayInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {

            if (data == 0) {
               goEnroll();
            }else if(data == 2){
                $.alert('您暂时没有进行中的借款单，无须还款！',function(){
                    window.location.href = 'index.html';

                });

            } else {
                data = JSON.parse(data);
                dataToForm(data);
            }
        }).error(function (msg) {
            aa = JSON.stringify(msg);
            $.alert('请检查您的网络!或者稍候再试');
        });
    }else{
     goEnroll();
    }
}

//将数据库中的字段渲染到页面里
function dataToForm(data){
$('#submit_time').html(data.submit_time);
    $('#total_fee').html(data.total_fee);
    $('#need_total_fee').html(data.single_fee*12);

    $('#left_total_fee').html(data.single_fee*12-data.has_payed-data.app_has_payed);

    $('#end_time').html(data.end_time);
    $('#money').val(data.single_fee);
    $('#trade_number').val(data.trade_number);
    dingdan = data.trade_number;

    $('#my_submit').attr('disabled',false);

    var money = $('#money');
    $('#plus').on('click',function(){
        if(money.val()<=parseInt(data.single_fee)){
            return false;
        }
        money.val(money.val()-data.single_fee)
    })
    $('#add').on('click',function(){
        if(money.val()>=data.single_fee*12-data.has_payed-data.app_has_payed){
            return false;
        }
        money.val(parseInt(money.val())+parseInt(data.single_fee));
    })
}

//提交表单
$('#my_submit').on('click',function(){
    var device = getFromwhereType();
    if(device == 'weixin'){
        goSubmitWeixin();
        var money = $('#money').val();
        window.location.href = 'http://www.xuefud.cn/index/index/torepayconfirm.html?token='+getToken()+'&money='+money;
    }else if(device == 'app'){
        //去选择支付宝支付还是微信支付
window.localStorage.setItem('dingdan',dingdan);
            window.location.href = 'apppay.html';



    }
})

//开始提交数据
function goSubmitWeixin(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'index/Index/toRepayConfirm.html',myForm,function(data){
        postSuccess(data,myForm);
    }).error(function(msg) {
        aa = JSON.stringify(msg);
    });
}

//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.tradeNumber = $('[name="my_trade_number"]').val();
    myForm.money = $('[name="money"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}

