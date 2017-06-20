

//执行初始化
certificationschoolInit();

//进入页面时初始化数据等
function certificationschoolInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/schoolInit.html', {
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
                window.localStorage.setItem('certificationschool', data);
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
    $('[name="school"]').val(data.school).attr('disabled',true);
    $('[name="department"]').val(data.department).attr('disabled',true);
    $('[name="class"]').val(data.class).attr('disabled',true);
    $('[name="professional"]').val(data.professional).attr('disabled',true);
    $('[name="sushe"]').val(data.sushe).attr('disabled',true);
    $('#xueshengzhengPic').attr('src', data.xueshengzhengUrl).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
    $('#xueshengzhengUrl').val('上传成功!').css({'color':'grey'});
    $('.pic_button').attr('disabled',true).css({'background':'grey'});
    $('#my_submit').attr('disabled',false).val('返回');
}


//获得学生证
function cameraGetXueshengzheng() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI
    });
    function onSuccess(imageURL) {
        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadXueshengzheng.html");
        uploadFile(imageURL,uri,'xueshengzheng');
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
                xueshengzhengSuccess(r);
        }else{
        }
    }

    //上传失败的回调
    function onError(error) {
$('.my_progess_show').hide();
            $('#xueshengzhengUrl').val('上传失败!请重新上传').css({'color':'red'});
    }
};

//学生证上传成功后显示
function xueshengzhengSuccess(r){
    var image = document.getElementById('xueshengzhengPic');
    image.src = r.response;
    $('#xueshengzhengUrl').val('上传成功').css({'color':'green'});
    $('#xueshengzhengPic').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

//提交表单
$('#my_submit').on('click',function(){
    if($(this).val()=='返回'){
        window.location.href = 'personaldata.html';
    }

    var flag = $('#xueshengzhengPic').attr('src');
    if(flag){
        goSubmit();
    }else{
        $.alert('您的学生证还没有上传！');
        return;
    }
})

//开始提交数据
function goSubmit(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/schoolHandle.html',myForm,function(data){
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
        window.localStorage.setItem('certificationschool',myForm);
        window.history.back();
    }else if(data==0){
        $.alert('信息提交失败，请稍候重试!');
    }else if(data==2){
        $.alert('您已经提交过，请等待管理人员审核！',function(){
            window.location.href='index.html';

        });

    }else{
        //$.alert(data);
    }
}


//得到表单数据
function  collectForm(){
    var myForm = {};
    myForm.school = $('[name="school"]').val();
    myForm.department = $('[name="department"]').val();
    myForm.class = $('[name="class"]').val();
    myForm.professional = $('[name="professional"]').val();
    myForm.sushe = $('[name="sushe"]').val();
    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}



