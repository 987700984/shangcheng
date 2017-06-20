/**
 * Created by Administrator on 2016/10/12 0012.
 */


$("#bank_name").picker({
    title: "请选择银行",
    cols: [
        {
            textAlign: 'center',
            values:['中国工商银行','招商银行','中国农业银行','中国银行','中国建设银行','中国民生银行','交通银行','中信银行','中国邮政储蓄银行']
        },
    ]
});



//执行初始化
bankerInit();

//进入页面时初始化数据等
function bankerInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/bankListInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if (data == 0) {
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

//银行卡号加密
function bankNumberSercet(){
    var secret = getByClass(document,'sercet_card_number');
    for(var i in secret){
        var temp = secret[i].innerHTML;
        console.log(temp);
        var replaceStr = '**********';
        var temp = temp.substr(10);
        console.log(temp);
        var tt = replaceStr +'' + temp;
        secret[i].innerHTML = tt;
    }
}


//将数据库中的字段渲染到页面里
function dataToForm(data){
    var html = '';
    for(var i in data){
        html += '<p style="padding: 10px;"><span style="color: #339;">' + data[i].bank_name + ':</span ><span style="color: #337a79;" class="sercet_card_number">' + data[i].card_number + '</span><br /><span  style="color: #339";>预留手机号:</span><span style="color: #337a79;">' + data[i].bank_phone + '</p>';
    }
    $('#my_wrap_js').html(html);
    var sum = getObjSum(data);
    if(sum>4){
        $('.hide_when_5').hide();
        $('#my_submit').val('返回');
    }
    bankNumberSercet();
}

//提交表单
$('#my_submit').on('click',function(){
    if($(this).val()=='返回'){
        window.location.href = 'personaldata.html';
    }else{
        goSubmit();
    }
})


//开始提交数据
function goSubmit(){
    myForm = collectForm();
    //jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/saveBankHandle.html',myForm,function(data){
        postSuccess(data,myForm);
    }).error(function(msg) {
        aa = JSON.stringify(msg);
        //alert(aa);
    });
}

//提交表单回调成功的操作
function postSuccess(data,myForm){
    if(data==1){
        $.alert('信息提交成功',function(){
            window.history.back();

        });

    }else if(data==0){
        $.alert('信息提交失败，请稍候重试!');
    }else if(data==2){
        $.alert('此卡您已经提交过，请勿重复提交',function(){
            window.location.href='auation.html';

        });

    }else{
        $.alert(data);
    }
}

//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.bank_name = $('[name="bank_name"]').val();
    myForm.bank_phone = $('[name="bank_phone"]').val();
    myForm.card_number = $('[name="card_number"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}






