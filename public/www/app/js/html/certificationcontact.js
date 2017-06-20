

//执行初始化方法
certificationcontactInit();

//进入页面时初始化数据等
function certificationcontactInit(){
    if(isEnrolled()) {
        $(function () {
            FastClick.attach(document.body);
        });
        jQuery.support.cors = true;
        var httpHost = getHttpHost();
        $.post(httpHost + '/index/Initdata/contactInit.html', {
            'token': getToken(),
            'fromwhere': getFromwhereType()
        }, function (data) {
            if (data == 3) {
                goEnroll();
            } else if (data == 2) {
                $('#my_submit').attr('disabled', false);
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

//将数据库中的字段渲染到表单里,并禁用；
function dataToForm(data) {
    $('[name="leaderName"]').val(data.leaderName).attr('disabled', true);
    $('[name="leaderPhone"]').val(data.leaderPhone).attr('disabled', true);
    $('[name="backupName"]').val(data.backupName).attr('disabled', true);
    $('[name="backupPhone"]').val(data.backupPhone).attr('disabled', true);
    $('[name="backupName1"]').val(data.backupName1).attr('disabled', true);
    $('[name="backupPhone1"]').val(data.backupPhone1).attr('disabled', true);
    $('[name="backupName2"]').val(data.backupName2).attr('disabled', true);
    $('[name="backupPhone2"]').val(data.backupPhone2).attr('disabled', true);
    $('[name="backupName3"]').val(data.backupName3).attr('disabled', true);
    $('[name="backupPhone3"]').val(data.backupPhone3).attr('disabled', true);
    $('[name="leaderRelation"]').val(getRelationFromNum(data.leaderRelation)).attr('disabled', true);
    $('[name="backupRelation"]').val(getRelationFromNum(data.backupRelation)).attr('disabled', true);
    $('[name="backupRelation1"]').val(getRelationFromNum(data.backupRelation1)).attr('disabled', true);
    $('[name="backupRelation2"]').val(getRelationFromNum(data.backupRelation2)).attr('disabled', true);
    $('[name="backupRelation3"]').val(getRelationFromNum(data.backupRelation3)).attr('disabled', true);
    $('.clickImg').removeAttr("onclick");
    $('#my_submit').attr('disabled', false).val('返回');
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
    $.post(httpHost+'/index/Index/contactHandle.html',myForm,function(data){
        postSuccess(data,myForm);
    }).error(function(msg) {
        //aa = JSON.stringify(msg);
        //alert(aa);
    });
}

//选择联系人
function chooseContact(name,phone){
    var aa = '#my_submit';
    navigator.contacts.pickContact(function(contact){
        var contactName = contact.name.formatted;
        var contactNumber = contact.phoneNumbers[0]['value'];
        $('[name='+name+']').val(contactName);
        $('[name='+phone+']').val(contactNumber);

    },function(err){
        console.log('Error: ' + err);
    });
}

//提交表单回调成功的操作
function postSuccess(data){
    if(data==1){
        $.alert('信息提交成功',function(){
            window.history.back();

        });


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
    myForm.leaderName = $('[name="leaderName"]').val();
    myForm.leaderRelation =getNumFromRelation($('[name="leaderRelation"]').val()) ;
    myForm.leaderPhone = $('[name="leaderPhone"]').val();

    myForm.backupName = $('[name="backupName"]').val();
    myForm.backupRelation = getNumFromRelation($('[name="backupRelation"]').val());
    myForm.backupPhone = $('[name="backupPhone"]').val();

    myForm.backupName1 = $('[name="backupName1"]').val();
    myForm.backupRelation1 = getNumFromRelation($('[name="backupRelation1"]').val());
    myForm.backupPhone1 = $('[name="backupPhone1"]').val();

    myForm.backupName2 = $('[name="backupName2"]').val();
    myForm.backupRelation2 = getNumFromRelation($('[name="backupRelation2"]').val());
    myForm.backupPhone2 = $('[name="backupPhone2"]').val();

    myForm.backupName3 = $('[name="backupName3"]').val();
    myForm.backupRelation3 = getNumFromRelation($('[name="backupRelation3"]').val());
    myForm.backupPhone3 = $('[name="backupPhone3"]').val();

    myForm.token = getToken();
    myForm.fromwhere =getFromwhereType();
    return myForm;
}


$("#leaderRelation").picker({
    title: "请选择直系联系人",
    cols: [
        {
            textAlign: 'center',
            values:['父母','配偶']
        },
    ]
});


$("#backupRelation").picker({
    title: "请选择备用联系人",
    cols: [
        {
            textAlign: 'center',
            values:['父母','配偶','兄弟姐妹','亲属','朋友','同学','同事']
        },
    ]
});

$("#backupRelation1").picker({
    title: "请选择备用联系人",
    cols: [
        {
            textAlign: 'center',
            values:['父母','配偶','兄弟姐妹','亲属','朋友','同学','同事']
        },
    ]
});


$("#backupRelation2").picker({
    title: "请选择备用联系人",
    cols: [
        {
            textAlign: 'center',
            values:['父母','配偶','兄弟姐妹','亲属','朋友','同学','同事']
        },
    ]
});


$("#backupRelation3").picker({
    title: "请选择备用联系人",
    cols: [
        {
            textAlign: 'center',
            values:['父母','配偶','兄弟姐妹','亲属','朋友','同学','同事']
        },
    ]
});

function getRelationFromNum(data){
    switch(data) {
        case '1': return '父母';
            break;
        case '2':return '配偶';
            break;
        case '3':return '兄弟姐妹';
            break;
        case '4':return '亲属';
            break;
        case '5':return '朋友';
            break;
        case '6':return '同学';
            break;
        case '7':return '同事';
            break;
        default:return false;
    }
}

function getNumFromRelation(data){
    switch(data) {
        case '父母': return 1;
            break;
        case '配偶':return 2;
            break;
        case '兄弟姐妹':return 3;
            break;
        case '亲属':return 4;
            break;
        case '朋友':return 5;
            break;
        case '同学':return 6;
            break;
        case '同事':return 7;
            break;
        default:return false;
    }
}