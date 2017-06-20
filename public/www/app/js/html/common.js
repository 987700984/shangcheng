/**
 *
 *
 *   常用功能函数
 *
 */


//获得用户识别号
function getToken(){
    return  window.localStorage.getItem('token')? window.localStorage.getItem('token'):'';
}

//获得主域名
function getHttpHost(){
    return 'http://www.dianddian.com';
}

//获得来源（'app'/'weixin')
function getFromwhereType(){
    return 'app';
}


//版本信息('android/ios')
function getVersion(){
    return {
        'name':'android',
        'version_id':1,
        'version_mini':0,
    };
}



//获得随机字符串
function randomString(len) {
    len = len || 32;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}


//是否登录
function isEnrolled(){
  return getToken()?true:false;
}




//通过类名获得元素
function getByClass(oParent, sClass)
{
    var aEle=oParent.getElementsByTagName('*');
    var aResult=[];
    var i=0;
    for(i=0;i<aEle.length;i++)
    {
        if(aEle[i].className==sClass)
        {
            aResult.push(aEle[i]);
        }
    }
    return aResult;
}

//获得对象元素个数
function getObjSum(obj){
    var j = 0;
    for(var i in obj){
        j++;
    }
    return j;
}


//获得审核进度
// data为从后台getProcess方法传回的参数
function getProcess(data){
    datat = JSON.stringify(data);
    var sum = 0;
    var arr =  [data.phone,data.school,data.contact,data.shenfenzheng,data.xinyong,data.xuexinwang];
    for(var i=0;i<arr.length;i++){

        if(arr[i]!=1){
            arr[i] = 0;
        }
        sum += parseInt(arr[i]);
    }
    return Math.ceil((sum/(arr.length))*100);
}



//去注册
function goEnroll(){
    $.alert('您还没登录，请先登录！',function(){
        window.location.href = 'user_login.html';

    })

}






//短信验证发送请求
$('#phoneMsg').on('click',function(){
    jQuery.support.cors = true;
    var httpHost = getHttpHost();
    var myForm = {
        'phoneNumber':$('[name="phone"]').val()
    };
    msgStatusChange();
    $.post(httpHost+'/index/index/getPhoneMsg.html',myForm,function(data){

        //data = JSON.parse(data);
        //
        //if(data[0]==0){
        //    $.alert(data[1]);
        //}else{
        //    //msgStatusChange();
        //}
    }).error(function(msg) {
        aa = JSON.stringify(msg);
        //alert(aa);
    });
})



//短信验证时状态切换
function msgStatusChange() {
    if ($('[name="phone"]').val() == '') {
        $.alert('手机号不能为空！');
        return false;
    }
    $('#phoneMsg').attr('disabled', true);
    var html = 60;
    var timer = setInterval(function () {
        if (html <= 0) {
            $('#phoneMsg').attr('disabled', false).html('发送验证码').css({'color': '#fff'});
            clearInterval(timer);

        } else {
            html--;
            $('#phoneMsg').html(html + 's后重发').css({'color': 'red'});
        };
    }, 1000);
}





//如果是APP，则加上顶部的导航条，若是微信，微信自带的有导航，不用加
(function(){
    if(getFromwhereType()=='app'){
        var tophtml = '<div > <div style="padding: 2px 10px;height: 60px;line-height: 80px;background-color:#00c78d" class="weui-row"> <div style="padding: 8px 0" id="my_head_left" class="weui-col-25"><img style="height: 22px;width:auto" src="../img/myback.png" alt=""></div> <div id="my_head_center" style="color: white;font-weight: bold;font-size: 20px" class="weui-col-50 text-center"></div> <div style="padding: 8px 0" id="my_head_right" class="weui-col-25 text-right"><img style="height: 22px;width:auto" src="../img/myhome.png" alt=""></div> </div> </div>';
        var mytitle = $('title').eq(0).html();
        if(mytitle!='主页'&&mytitle!='我的'){

            $('body').eq(0).prepend(tophtml);
            $('#my_head_center').html(mytitle);
            $('#my_head_left').on('click',function(){
                window.history.back();
            });
            $('#my_head_right').on('click',function(){
                window.location.href = 'index.html';
            })
        }
    }
})()


