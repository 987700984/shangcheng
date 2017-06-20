

//执行初始化
certificationidInit();


//进入页面时初始化数据等
function certificationidInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });

        $("#end").cityPicker({
            title: "选择：省-市-区"
        });

        jQuery.support.cors = true;
        var httpHost = getHttpHost();

        $.post(httpHost + '/index/Initdata/idCardInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if (data == 3) {
                goEnroll();
            } else if (data == 0) {
                window.location.href = 'mtel.html';
            } else if (data == 2) {
                $('#my_submit').attr('disabled', false);
            } else {
                window.localStorage.setItem('certificationid', data);
                data = JSON.parse(data);
                dataToForm(data);
            }
        }).error(function (msg) {
            aa = JSON.stringify(msg);
        });
    }else{
       goEnroll();
    }
}


//将数据库中的字段渲染到表单里,并禁用；
function dataToForm(data){

    $('[name="realName"]').val(data.realName).attr('disabled',true);
    $('[name="idNumber"]').val(data.idNumber).attr('disabled',true);
    $('[name="email"]').val(data.email).attr('disabled',true);
    $('[name="wholeAddress"]').val(data.wholeAddress).attr('disabled',true);
    $('[name="detailAddress"]').val(data.detailAddress).attr('disabled',true);
    $('[name="sex"] option').each(function(index,data){
        if($(data).val()==data.sex){
            $(data).attr('selected',true);
        }
    })
    $('[name="sex"]').attr('disabled',true);
    $('#frontPic').attr('src', data.frontUrl).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#backPic').attr('src',data.backUrl).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#front_url').val('上传成功!').css({'color':'grey'});
    $('#back_url').val('上传成功!').css({'color':'grey'});
    $('.pic_button').attr('disabled',true).css({'background':'grey'});
    $('#my_submit').attr('disabled',false).val('返回');
}


//获得身份证正面
function cameraGetFront() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadIdFrontUrl.html");
        uploadFile(imageURL,uri,'front_url');
    }
    function onFail(message) {
        //alert('Failed because: ' + message);
    }
}

//获得身份证背面
function cameraGetBack() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadIdBackUrl.html");
        uploadFile(imageURL,uri,'back_url');
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
            if(imgType=='front_url'){
                frontSuccess(r);
            }else{
                backSuccess(r);
            }
        }else{
        }
    }

    //上传失败的回调
    function onError(error) {
        $('.my_progess_show').hide();
        if(imgType=='front_url'){
            $('#front_url').val('上传失败!请重新上传').css({'color':'red'});
        }else{
            $('#back_url').val('上传失败!请重新上传').css({'color':'red'});
        }
    }
};

//身份证正面的成功后显示
function frontSuccess(r){
    var image = document.getElementById('frontPic');
    image.src = r.response;
    $('#front_url').val('上传成功!').css({'color':'green'});
    $('#frontPic').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

//身份证反面的成功后显示
function backSuccess(r){
    var image = document.getElementById('backPic');
    image.src = r.response;
    $('#back_url').val('上传成功!').css({'color':'green'});
    $('#backPic').attr('src',r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

//提交表单
$('#my_submit').on('click',function(){
    if($(this).val()=='返回'){
        window.location.href = 'personaldata.html';
    }

    var flag = $('#frontPic').attr('src')&& $('#backPic').attr('src');
    if(flag){
        goSubmit();
    }else{
        $.alert('您的身份证正反面都要上传！您还没有全部上传！');
        return;
    }
})

//开始提交数据
function goSubmit(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();

    $.post(httpHost+'/index/Index/idCardHandle.html',myForm,function(data){
      postSuccess(data,myForm);
    }).error(function(msg) {
        aa = JSON.stringify(msg);

    });
}

//提交表单回调成功的操作
function postSuccess(data,myForm){
    if(data==1){
        $.alert('信息提交成功');
        myForm.clientSubmit = 1;
        var myForm = JSON.stringify(myForm);
        window.localStorage.setItem('certificationid',myForm);
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
    myForm.realName = $('[name="realName"]').val();
    myForm.idNumber = $('[name="idNumber"]').val();
    myForm.email = $('[name="email"]').val();
    myForm.wholeAddress = $('[name="wholeAddress"]').val();
    myForm.detailAddress = $('[name="detailAddress"]').val();
    myForm.sex = $('[name="sex"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}



