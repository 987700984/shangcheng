/**
 *
 *
 *   作者：***
 *
 */


//执行初始化
personaldataInit()
//初始化函数
function personaldataInit(){
    if(isEnrolled()){

        urlInit();
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        myForm = collectForm();
        $.post(httpHost+'/index/initdata/personaldataInit.html',myForm,function(data){
            postSuccess(data);
        }).error(function(msg) {
            //aa = JSON.stringify(msg);
            //alert(aa);
        });
    }else{
        goEnroll();
    }
}

function urlInit(){
    var httpHost = getHttpHost();
    if(getFromwhereType()=='weixin'){
        $('#idUrl').attr('href',httpHost+'/index/weixin/certificationId.html?token='+getToken());
        $('#phoneUrl').attr('href',httpHost+'/index/weixin/certificationPhone.html?token='+getToken());
        $('#schoolUrl').attr('href',httpHost+'/index/weixin/certificationSchool.html?token='+getToken())
    }
}


//收集post参数
function collectForm(){
    var myForm = {};
    var token = getToken();
    if(!token){
      goEnroll();
    }else{
        myForm.token = token;
        myForm.fromwhere = getFromwhereType();
        return myForm;
    }
}

//初始化成功的回调
function postSuccess(data){
    if(data==0){
       goEnroll();
    }else{
        data = JSON.parse(data);
        dataToDocument(data);
        dataShowIcons(data);
    }
}

function dataShowIcons(data){
    var arr = ['phone','school','contact','shenfenzheng','xinyong','xuexinwang'];
    //$('#phone').removeClass().addClass('weui_icon_waiting');
    for(var i in data[1]){
        if(data[1][i]==1){
            $('#'+i).removeClass().addClass('weui_icon_waiting');
        }
    }
    for(var i in data[0]){
        if(data[0][i]==1){
            $('#'+i).removeClass().addClass('weui_icon_success');
        }
    }
}

function dataToDocument(data){
    var pass = getProcess(data[0]);
    var submit = getProcess(data[1]);
    $('#submitProcess').css({'width':submit + '%'});
    $('#submitWord').html(submit + '%');
    $('#passProcess').css({'width':pass + '%'});
    $('#passWord').html(pass + '%');
}








