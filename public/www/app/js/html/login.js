/**
 * Created by Administrator on 2016/10/12 0012.
 */


    //提交按钮点击后的操作
$('#my_submit').on('click',function(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/index/loginHandle.html',myForm,function(data){
        data = JSON.parse(data);
        postSuccess(data,myForm);
    }).error(function(msg) {
        aa = JSON.stringify(msg);
    });
})





//收集表单数据
function collectForm(){
    var myForm = {};
    myForm.phone = $('[name="phone"]').val();
    myForm.password = $('[name="password"]').val();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}



//回调成功的操作
function postSuccess(data,myForm){
    if(data['status']==1){
        $.alert('登录成功！',function(){
            window.localStorage.setItem('token',data['message']);


var host = document.referrer;


if(getFromwhereType()=='app'){
    window.history.back();
}else{

    if(host.indexOf( getHttpHost())==-1){
        window.location.href = 'torepay.html';
    }else{
        window.history.back();
    }


}



        //5471869230
        });


    }else if(data['status']==2){
        $.alert('此手机号还未注册，请先去注册',function(){
            window.location.href='mtel.html';

        });

    }else if(data['status']==0){
        $.alert(data['message']);
    }
}




