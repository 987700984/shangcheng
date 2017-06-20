

//执行初始化
apppayInit();

//进入页面时初始化数据等
function apppayInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/apppay.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {

            if (data == 3) {
                goEnroll();
            } else {
data = JSON.parse(data);

                dataToForm(data);
            }
        }).error(function (msg) {
            aa = JSON.stringify(msg);
            alert(aa);
            $.alert('请检查您的网络!或者稍候再试');
        });
    }else{
        goEnroll();
    }

}

//将数据库中的字段渲染到页面里；
function dataToForm(data){

    $('#amount_name').html(data.name);
    $('#amount_bank').html(data.bank);
    $('#amount_num').html(data.amount_num);

    $('#my_submit').attr('disabled',false);
}


//获得拍照的支付小票
function cameraGetPayPic() {
    navigator.camera.getPicture(onSuccess, onFail, { quality: 50,
        destinationType: Camera.DestinationType.FILE_URI
    });
    function onSuccess(imageURL) {

        var httpHost = getHttpHost();
        var uri = encodeURI(httpHost+"/index/xuefuapp/uploadPaypic.html");
        uploadFile(imageURL,uri,'paypic');
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

    options.params = {'dingdan':window.localStorage.getItem('dingdan')};
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

//支付小票上传成功后显示
function xueshengzhengSuccess(r){
    var image = document.getElementById('apppayPic');
    image.src = r.response;
    $('#paypicUrl').val('上传成功').css({'color':'green'});
    $('#payButton').attr('disabled',true).css({'background':'#777'});
    $('#apppayPic').attr('src', r.response).css({display:'block',width:'auto',height:200+'px',padding:10+'px'});
};

//提交表单
$('#my_submit').on('click',function(){
        window.location.href = 'index.html';
})

//开始提交数据
function goSubmit(){
    myForm = collectForm();
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    $.post(httpHost+'/index/Index/payPic.html',myForm,function(data){
      postSuccess(data,myForm);
    }).error(function(msg) {
        //aa = JSON.stringify(msg);
        //alert(aa);
    });
}

//提交表单回调成功的操作
function postSuccess(data,myForm){
    if(data==1){
        $.alert('支付小票提交成功');
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

    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}



