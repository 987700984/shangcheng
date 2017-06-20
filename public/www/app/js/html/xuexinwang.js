

//执行初始化
certificationxuexinInit();

//进入页面时初始化数据等
function certificationxuexinInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/xuexinInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if (data == 0) {
                $('#my_submit').attr('disabled', false);
            } else {
                window.localStorage.setItem('xuexinwang', data);
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

//将数据库中的字段渲染到表单里,并禁用；
function dataToForm(data){
    $('[name="username"]').val(data.username).attr('disabled',true);
    $('[name="password"]').val(data.password).attr('disabled',true);
    $('#my_submit').attr('disabled',false).val('返回');
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
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/xuexinHandle.html',myForm,function(data){
        postSuccess(data,myForm);
    }).error(function(msg) {
        //aa = JSON.stringify(msg);
        //alert(aa);
    });
}

//提交表单回调成功的操作
function postSuccess(data,myForm){
    if (data == 3) {
        goEnroll();
    } else if(data==1){
        $.alert('信息提交成功');
        myForm.clientSubmit = 1;
        var myForm = JSON.stringify(myForm);
        window.localStorage.setItem('certificationxuexin',myForm);
        window.history.back();
    }else if(data==0){
        $.alert('信息提交失败，请稍候重试!');
    }else if(data==2){
        $.alert('您已经提交过，请等待管理人员审核！',function(){
            window.location.href='index.html';

        });

    }else{
        $.alert(data);
    }
}

//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.username = $('[name="username"]').val();
    myForm.password = $('[name="password"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}



