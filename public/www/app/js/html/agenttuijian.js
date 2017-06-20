/**
 * Created by Administrator on 2016/10/12 0012.
 */
/**
 * Created by Administrator on 2016/10/12 0012.
 */



//执行初始化
agentInit();

//进入页面时初始化数据等
function agentInit(){
    if(isEnrolled()) {
    $(function () {
        FastClick.attach(document.body);
    });
    jQuery.support.cors = true;  //在.Ajax()定义前设置jQuery.Support.Cors = true
                                // 在.Ajax()执行完毕之前让其它button等触发事件暂停等待
    var httpHost = getHttpHost();
    $.post(httpHost + '/index/Initdata/agentInit.html', {
        'token': getToken(),
        'fromwhere': getFromwhereType()
    }, function (data) {
        if (data == 0) {
            $('#my_submit').attr('disabled',false);
            $('#my_agent').append('请填写您的个人信息，公司工作人员稍候会联系您，如果通过审核，记得让您推荐的客户注册时填上您的邀请码')
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
    $('[name="school"]').val(data.school).attr('disabled',true);
    $('[name="class"]').val(data.class).attr('disabled',true);
    $('[name="email"]').val(data.email).attr('disabled',true);
    $('[name="realName"]').val(data.realName).attr('disabled',true);
    $('[name="sushe"]').val(data.sushe).attr('disabled',true);
    $('#my_submit').attr('disabled',false).val('返回');
    if(data.isSuccess==1){
        $('#my_agent').append('您的邀请码是:<span style="color: #CD3278;font-size: 16px">'+data.recommendNum+'</span>&nbsp;<a id="showAgentList" href="myagentlist.html" style="color: #999;text-decoration: underline">&nbsp;&nbsp;查看我推荐的会员</a>')
    }else {
        $('#my_agent').append('等待审核中......')
    }
}

//提交表单
$('#my_submit').on('click',function(){
    if($(this).val()=='返回'){
        window.location.href = 'auation.html';
    }else{
        goSubmit();
    }
})

//开始提交数据
function goSubmit(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/agentHandle.html',myForm,function(data){
        postSuccess(data);
    }).error(function(msg) {
        aa = JSON.stringify(msg);
        alert(aa);
    });
}

//提交表单回调成功的操作
function postSuccess(data){
    if(data==1){
        $.alert('信息提交成功,稍候会有工作人员联系您，请保持电话畅通！',function(){
            var myForm = JSON.stringify(myForm);

                window.history.back();

        });


    }else if(data==0){
        $.alert('信息提交失败，请稍候重试!');
    }else if(data==2){
        $.alert('您已经提交过，请勿重复提交',function(){

                window.location.href='auation.html';

        });

    }else{
        $.alert(data);
    }
}

//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.school = $('[name="school"]').val();
    myForm.class = $('[name="class"]').val();
    myForm.realName = $('[name="realName"]').val();
    myForm.sushe = $('[name="sushe"]').val();
    myForm.email = $('[name="email"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}






