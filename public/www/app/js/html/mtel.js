/**
 * Created by Administrator on 2016/10/12 0012.
 */


 //提交按钮点击后的操作
$('#my_submit').on('click',function(){
    if($('#my_switch')[0].checked){
        myForm = collectForm();
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost+'/index/index/mTelHandle.html',myForm,function(data){
            data = JSON.parse(data);
           postSuccess(data);
        }).error(function(msg) {
            aa = JSON.stringify(msg);
          //alert(aa);
        });
    }else{
        $.alert('同意接受服务条款与协议后才能提交哦！')
    };
})

//收集表单数据
function collectForm(){
    var myForm = {};
    myForm.phone = $('[name="phone"]').val();
    myForm.checkNum = $('[name="checkNum"]').val();
    myForm.password = $('[name="password"]').val();
    myForm.repassword = $('[name="repassword"]').val();
    myForm.recommendNum = $('[name="recommendNum"]').val();
    myForm.fromwhere = getFromwhereType();
    return myForm;
}

//回调成功的操作
function postSuccess(data){
     if(data['status']==0||data['status']==3){
        $.alert(data['message']);
    }else if(data['status']==2){
        $.alert('您已经注册过!可以直接登录！',function(){
                window.localStorage.setItem('token',data['message']);
                window.location.href = 'auation.html';

            }
            );

    }else if(data['status']==1){
        $.alert('信息提交成功',function(){
            window.localStorage.setItem('token',data['message']);
            window.location.href = 'auation.html';


        });

    }
}






