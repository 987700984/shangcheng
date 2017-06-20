/**
 * Created by Administrator on 2016/10/14 0014.
 */


//执行初始化
certificationsesameInit();




//进入页面时初始化数据等
function certificationsesameInit(){
    if(isEnrolled()) {
    $(function () {
        FastClick.attach(document.body);
    });
        jQuery.support.cors = true;

    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Initdata/zhimaInit.html',{'token': getToken(), 'fromwhere': getFromwhereType()}, function (data) {

        if (data == 3) {
            goEnroll();
        } else if(data==1){
            $('#my_submit').attr('disabled',false);
        }else if(data==0){
            $.alert('身份验证通过后才可以进行芝麻认证哦，请耐心等待后台审核！',function(){
                window.location.href = 'auation.html';

            })

        } else{
            dataToForm(data);

        }
    }).error(function(msg) {
        aa = JSON.stringify(msg);

        //$.alert('请检查您的网络!或者稍候再试');
    });
    }else{
        goEnroll();
    }
}

//将返回的字段渲染到页面；
function dataToForm(data){
    $('#showSesame').show();
    $('#showSpan').html(data);
    //$('#my_submit').attr('disabled',false);
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


    var httpHost = getHttpHost() + '/zhima/index/index.html?token='+getToken()+'&fromwhere='+getFromwhereType();

    //if(getFromwhereType()=='weixin'){
    //    window.location.href = httpHost ;
    //}else{
        $('#test').attr('src',httpHost);
        $('#testwrap').show().css({'position':'fixed','left': 0,'top': 0,'bottom':0,'right':0});
    //}
    var timer = setInterval(function(){
        var i1 = window.frames['test'];
        var val=i1.document.getElementById("myzhima").value;
        if(val==1){

            clearInterval(timer);
            setTimeout(function(){
                $('#testwrap').hide();
                window.location.href = 'personaldata.html';
            },800)

        }
    },200);



    //var httpHost = getHttpHost();
    //window.location.href = httpHost + '/zhima/index/index.html?token='+getToken()+'&fromwhere='+getFromwhereType();

}

//提交表单回调成功的操作
function postSuccess(data){
    dataToForm(data);
    //if(data==1){
    //    $.alert('信息提交成功');
    //    setTimeout(function(){
    //        window.history.back();
    //    },2000)
    //
    //}else if(data==0){
    //    $.alert('信息提交失败，请稍候重试!');
    //}else if(data==2){
    //    $.alert('您已经提交过，请等待管理人员审核！');
    //    setTimeout(function(){
    //        window.location.href='index.html';
    //    },2000)
    //}else{
    //    alert(data);
    //}
}

//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}


