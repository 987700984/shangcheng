/**
 * Created by Administrator on 2016/10/12 0012.
 */


 //提交按钮点击后的操作
$('#my_submit').on('click',function(){
        myForm = collectForm();
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost+'/index/index/changePasswordHandle.html',myForm,function(data){
            data = JSON.parse(data);
           postSuccess(data);
        }).error(function(msg) {
            aa = JSON.stringify(msg);
          //alert(aa);
        });
})

//收集表单数据
function collectForm(){
    var myForm = {};
    myForm.phone = $('[name="phone"]').val();
    myForm.checkNum = $('[name="checkNum"]').val();
    myForm.password = $('[name="password"]').val();
    myForm.repassword = $('[name="repassword"]').val();
    myForm.fromwhere = getFromwhereType();
    return myForm;
}

//回调成功的操作
function postSuccess(data){
     if(data['status']==3){
        $.alert(data['message']);
    }else if(data['status']==2){
        $.alert(data['message'],function(){
            window.location.href = 'mtel.html';

        });

    }else if(data['status']==1){
        $.alert('密码修改成功!',function(){
            window.localStorage.setItem('token',data['message']);
            window.location.href = 'auation.html';
        });

    }
}

