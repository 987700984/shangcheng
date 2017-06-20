


console.log('asdfasdfasdfasdfasdfasdfasdfasd');

//初始化数据
var httpHost = getHttpHost();
$.post(httpHost+'/app/Index/index',collectForm(),function(data){

      data = JSON.parse(data);

    if(data.data['banner']){
        var l='';
        var banner=data.data['banner']
        var length=banner.length;
        for(var i=0;i<length;i++){
            l+='<div class="swiper-slide"><a href="javascript:;"><img src="'+banner[i]['image_url']+'" alt=""></a></div>';
        }
        $('#banner').html(l);
    }
    if(data.data['list']){
        var k='';
        var list=data.data['list'];
        for(var i=0;i<list.length;i++){
            k+=' <div class="centent-box" id="list"><div class="centent-list"><div class="list-img"> <a href="ruhui_centent.html?id='+list[i]['goods_id']+'"><img src="'+list[i]['image']+'" alt=""></a></div><div class="list-text">';
            k+='<div class="title"><a href="ruhui_centent.html?id='+list[i]['goods_id']+'">'+list[i]['name']+'</a></div>';
            k+='<p class="money">秒杀价：￥'+list[i]['price']+'</p><p><s>￥'+list[i]['origin_price']+'</s></p><p><span><i class="icon iconfont icon-daojishi"></i></span> <span>离结束还剩：</span>';
            k+='<span class="lxftime" endtime="'+list[i]['end_time']+'"></span></p></div></div>';

        }
        if(data.data['lis']){

            var lis=data.data['lis'];
            for(i=0;i<=lis.length;i++){
                k+=' <div class="centent-box" id="list"><div class="centent-list"><div class="list-img"> <a href="ruhui_centent.html?id='+lis[i]['goods_id']+'"><img src="'+lis[i]['image']+'" alt=""></a></div><div class="list-text">';
                k+='<div class="title"><a href="ruhui_centent.html?id='+lis[i]['goods_id']+'">'+lis[i]['name']+'</a></div>';
                k+='<p class="money">秒杀价：￥'+lis[i]['price']+'</p><p><s>￥'+lis[i]['origin_price']+'</s></p><p><span><i class="icon iconfont icon-daojishi"></i></span> <span>离结束还剩：</span>';
                k+='<span class="lxftime" endtime="'+lis[i]['end_time']+'"></span></p></div></div>';

            }
        }
        $('#list').html(k);

    }




    $(".swiper-container").swiper({
        loop: true,
        autoplay: 3000
    });

    $(".swiper-containers").swiper({
        loop: true,
        autoplay: 0
    });

    document.getElementById("index").className = "active";

}).error(function(msg) {

    //alert(aa);
});



//检查版本
$.post(httpHost+'/index/Initdata/check_update.html',getVersion(),function(data) {
    if(getFromwhereType()=='weixin'){
        return false;
    }

/* IOS
data = JSON.parse(data);

if(data.status==1){

    $.confirm('发现新版本，可以马上更新',function(){

        downloadAPP(data.version.app_url);
    })
}

*/


}).error(function(){
    aa = JSON.stringify(msg);

});




//得到表单数据
function  collectForm(){
    var myForm = {};

    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}





//APP下载

function downloadAPP(url) {

    var fileTransfer = new FileTransfer();
    var uri = encodeURI(url);
    var fileURL =  "///storage/emulated/0/DCIM/xuefudai.apk";
    fileTransfer.onprogress = function(progressEvent) {
        navigator.notification.progressStart("新版本下载", "当前下载进度");
        fileTransfer.onprogress = function(progressEvent) {
            if (progressEvent.lengthComputable) {
                navigator.notification.progressValue(Math.ceil(( progressEvent.loaded / progressEvent.total )*100));
            } else {
                loadingStatus.increment();
            }
        };

    };
    fileTransfer.download(
        uri, fileURL, function(entry) {
            toInstall(entry);
            console.log("download complete: " + entry.toURL());
        },

        function(error) {
            console.log("download error source " + error.source);
            console.log("download error target " + error.target);
            console.log("download error code" + error.code);
        },

        false, {
            headers: {
                "Authorization": "Basic dGVzdHVzZXJuYW1lOnRlc3RwYXNzd29yZA=="
            }
        }
    );
}


//安装新下载的软件
function toInstall(entry){
    cordova.plugins.fileOpener2.open(
        entry.nativeURL,
        'application/vnd.android.package-archive',
        {
            error : function(e) {
                alert(JSON.stringify(e));
                console.log('Error status: ' + e.status + ' - Error message: ' + e.message);
            },
            success : function () {
                console.log('file opened successfully');
            }
        }
    );
}














