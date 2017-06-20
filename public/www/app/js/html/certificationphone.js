

//执行初始化
certificationphoneInit();


//进入页面时初始化数据等
function certificationphoneInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/phoneInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if (data == 3) {
                goEnroll();
            } else if (data == 0) {
                window.location.href = 'mtel.html';
            } else if (data == 2) {
                $('#my_submit').attr('disabled', false);
            } else{
                data = JSON.parse(data);
                dataToForm(data);
            }
        }).error(function (msg) {
            //aa = JSON.stringify(msg);
            $.alert('请检查您的网络!或者稍候再试');
        });
    }else{
      goEnroll();
    }
}

//将数据库中的字段渲染到表单里,并禁用；
function dataToForm(data){
    $('[name="phoneServePassword"]').val(data.phoneServePassword).attr('disabled',true);
    $('#phonePic1').attr('src', data.phoneRecordUrl1).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#phonePic2').attr('src', data.phoneRecordUrl2).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#phonePic3').attr('src', data.phoneRecordUrl3).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#phonePic4').attr('src', data.phoneRecordUrl4).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#phone_record_url1').val('上传成功!').css({'color':'grey'});
    $('#phone_record_url2').val('上传成功!').css({'color':'grey'});
    $('#phone_record_url3').val('上传成功!').css({'color':'grey'});
    $('#phone_record_url4').val('上传成功!').css({'color':'grey'});
    $('.pic_button').attr('disabled',true).css({'background':'grey'});
    $('#my_submit').attr('disabled',false).val('返回');
}

//获得手机通话记录1
function cameraGetPhone1() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI,
        sourceType: Camera.PictureSourceType.PHOTOLIBRARY
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadPhoneUrl1.html");
        uploadFile(imageURL,uri,'phonePic1');
    }
    function onFail(message) {
        //alert('Failed because: ' + message);
    }
}

//获得手机通话记录2
function cameraGetPhone2() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI,
        sourceType: Camera.PictureSourceType.PHOTOLIBRARY
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadPhoneUrl2.html");
        uploadFile(imageURL,uri,'phonePic2');
    }
    function onFail(message) {
        //alert('Failed because: ' + message);
    }
}

//获得手机通话记录1
function cameraGetPhone3() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI,
        sourceType: Camera.PictureSourceType.PHOTOLIBRARY
    });
    function onSuccess(imageURL) {

        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadPhoneUrl3.html");
        uploadFile(imageURL,uri,'phonePic3');
    }
    function onFail(message) {
        //alert('Failed because: ' + message);
    }
}

//获得手机通话记录1
function cameraGetPhone4() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI,
        sourceType: Camera.PictureSourceType.PHOTOLIBRARY
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadPhoneUrl4.html");
        uploadFile(imageURL,uri,'phonePic4');
    }
    function onFail(message) {
        //alert('Failed because: ' + message);
    }
}

//执行上传图片
function uploadFile(fileURL,uri,imgType) {
    var options = new FileUploadOptions();
    var token = getToken();
    options.fileKey = "file";
    options.fileName =token;
    options.mimeType = "image/jpeg";
    var headers = {'headerParam':'headerValue'};
    options.headers = headers;
    var ft = new FileTransfer();
   ft.onprogress = function(progressEvent) {
    if (progressEvent.lengthComputable) {

        $('.my_progess_show').show();
     
    } else {
        $('.my_progess_show').show();
    }
};
    ft.upload(fileURL, uri, onSuccess, onError, options);
    //上传成功的回调
    function onSuccess(r) {
$('.my_progess_show').hide();
        if(r.responseCode==200){
            if(imgType=='phonePic1'){
                phonePic1Success(r);
            }else  if(imgType=='phonePic2'){
                phonePic2Success(r);
            }else  if(imgType=='phonePic3'){
                phonePic3Success(r);
            }else {
                phonePic4Success(r);
            }
        
        }else{
    
        }
    }

    //上传失败的回调
    function onError(error) {
$('.my_progess_show').hide();
        if(imgType=='phonePic1'){
            $('#phone_record_url1').val('上传失败!请重新上传!').css({'color':'red'});
        }else  if(imgType=='phonePic2'){
            $('#phone_record_url1').val('上传失败!请重新上传!').css({'color':'red'});
        } else  if(imgType=='phonePic3'){
            $('#phone_record_url1').val('上传失败!请重新上传!').css({'color':'red'});
        }else {
            $('#phone_record_url1').val('上传失败!请重新上传!').css({'color':'red'});
        }
    }
};

//phone1的成功后显示
function phonePic1Success(r){
    $('#phone_record_url1').val('上传成功!').css({'color':'green'});
    $('#phonePic1').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

function phonePic2Success(r){
    $('#phone_record_url2').val('上传成功!').css({'color':'green'});
    $('#phonePic2').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

function phonePic3Success(r){
    $('#phone_record_url3').val('上传成功!').css({'color':'green'});
    $('#phonePic3').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

function phonePic4Success(r){
    $('#phone_record_url4').val('上传成功!').css({'color':'green'});
    $('#phonePic4').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

//提交表单
$('#my_submit').on('click',function(){
    if($(this).val()=='返回'){
        window.location.href = 'personaldata.html';
    }
    var flag = $('#phonePic1').attr('src')&& $('#phonePic2').attr('src')&& $('#phonePic3').attr('src')&& $('#phonePic4').attr('src');

    if(flag){
        goSubmit();
    }else{
        $.alert('您的手机截图需要4张，您还没有全部上传！');
        return;
    }
})

//开始提交数据
function goSubmit(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/phoneHandle.html',myForm,function(data){
      postSuccess(data,myForm);
    }).error(function(msg) {
        //aa = JSON.stringify(msg);
        //alert(aa);
    });
}

//提交表单回调成功的操作
function postSuccess(data,myForm){
    if(data==1){
        $.alert('信息提交成功');
        myForm.clientSubmit = 1;
        var myForm = JSON.stringify(myForm);
        window.localStorage.setItem('certificationphone',myForm);
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
    myForm.phoneServePassword = $('[name="phoneServePassword"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}




