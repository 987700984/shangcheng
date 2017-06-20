function pwForm() {
    var yzm_pw = document.getElementById("yzm_input_pw").value;
    var pw1_pw = document.getElementById("pw1_input_pw").value;
    var pw2_pw = document.getElementById("pw2_input_pw").value;

    if (yzm_pw == "") {
        document.getElementById("yzm_input_pw").className = "myfunction-input";
        document.getElementById("yzm-pw").innerHTML = "验证码不能为空";
        return false;
    }
    else {
        document.getElementById("yzm_input_pw").className = "form-control";
        document.getElementById("yzm-pw").innerHTML = "";
    }


    if (pw1_pw == "") {
        document.getElementById("pw1_input_pw").className = "myfunction-input";
        document.getElementById("pw-1").innerHTML = "验证码不能为空";
        return false;
    }
    else {
        document.getElementById("pw1_input_pw").className = "form-control"
        document.getElementById("pw-1").innerHTML = "";
    }

    if (pw1_pw != pw2_pw) {
        document.getElementById("pw2_input_pw").className = "myfunction-input"
        document.getElementById("pw-2").innerHTML = "两次密码不相同";
        return false;
    }
    else {
        document.getElementById("pw2_input_pw").className = "form-control"
        document.getElementById("pw-2").innerHTML = "";
    }
}


function yzForm() {
    var pw_yz = document.getElementById("pw_input_yz").value;
    var mobile_yz = document.getElementById("mobile_input_yz").value;
    var yzm_yz = document.getElementById("yzm_input_yz").value;

    var mobile_phone1 = /^1[3|4|5|7|8]\d{9}$/;

    if (pw_yz == "") {
        document.getElementById("pw_input_yz").className = "myfunction-input";
        document.getElementById("password-yz").innerHTML = "登录密码不能为空";
        return false;
    }
    else {
        document.getElementById("pw_input_yz").className = "form-control";
        document.getElementById("password-yz").innerHTML = "";
    }


    if (!mobile_phone1.test(mobile_yz)) {
        document.getElementById("mobile_input_yz").className = "myfunction-input"
        document.getElementById("mobile-yz").innerHTML = "手机号码不正确";
        return false;
    }
    else {
        document.getElementById("mobile_input_yz").className = "form-control"
        document.getElementById("mobile-yz").innerHTML = "";
    }

    if (yzm_yz == "") {
        document.getElementById("yzm_input_yz").className = "myfunction-input";
        document.getElementById("yzm-yz").innerHTML = "验证码不能为空";
        return false;
    }
    else {
        document.getElementById("yzm_input_yz").className = "form-control";
        document.getElementById("yzm-yz").innerHTML = "";
    }

}

//去掉特殊符号的方法（调用在下面）
String.prototype.TextFilter = function () {
    var pattern = new RegExp("[`~%!@#^=''?~！@#￥……&——‘”“'？*()（），+-,。.、<>']"); //[]内输入你要过滤的字符
    var rs = "";
    for (var i = 0; i < this.length; i++) {
        rs += this.substr(i, 1).replace(pattern, '');
    }
    return rs;
}

function checkChar() {
    var uname = document.getElementById("my_name").value; //通过ID取到my_name的值
    var txt = uname.TextFilter(); //调用上面的去字符方法
    if (txt != uname) {
//            alert("您输入的内容含有限定字符");
        document.getElementById("my_name").className = "myfunction-input";
        document.getElementById("sp-name").innerHTML = "昵称不能含有特殊符号";
        return false;
    }
}



// 头像上传裁剪


(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node / CommonJS
        factory(require('jquery'));
    } else {
        factory(jQuery);
    }
})(function ($) {

    'use strict';

    var console = window.console || { log: function () {} };

    function CropAvatar($element) {
        this.$container = $element;

        this.$avatarView = $('.ibox-contents');
        this.$avatar = $('.Head_portrait');
        this.$avatarModal = $("body").find('#avatar-modal');
        this.$loading = $("#page-wrapper").find('.loading');

        this.$avatarForm = this.$avatarModal.find('.avatar-form');
        this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
        this.$avatarSrc = this.$avatarForm.find('.avatar-src');
        this.$avatarData = this.$avatarForm.find('.avatar-data');
        this.$avatarInput = this.$avatarForm.find('.avatar-input');
        this.$avatarSave = this.$avatarForm.find('.avatar-save');
        this.$avatarBtns = this.$avatarForm.find('.avatar-btns');

        this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
        this.$avatarPreview = this.$avatarModal.find('.avatar-preview');

        this.init();
    }

    CropAvatar.prototype = {
        constructor: CropAvatar,
        support: {
            fileList: !!$('<input type="file">').prop('files'),
            blobURLs: !!window.URL && URL.createObjectURL,
            formData: !!window.FormData
        },

        init: function () {
            this.support.datauri = this.support.fileList && this.support.blobURLs;

            if (!this.support.formData) {
                this.initIframe();
            }

            this.initTooltip();
            this.initModal();
            this.addListener();
        },

        addListener: function () {
            this.$avatarView.on('click', $.proxy(this.click, this));
            this.$avatarInput.on('change', $.proxy(this.change, this));
            this.$avatarForm.on('submit', $.proxy(this.submit, this));
            this.$avatarBtns.on('click', $.proxy(this.rotate, this));
        },

        initTooltip: function () {
            this.$avatarView.tooltip({
                placement: 'bottom'
            });
        },

        initModal: function () {
            this.$avatarModal.modal({
                show: false
            });
        },

        initPreview: function () {
            var url = this.$avatar.attr('src');

            this.$avatarPreview.empty().html('<img src="' + url + '">');
        },

        initIframe: function () {
            var target = 'upload-iframe-' + (new Date()).getTime(),
                $iframe = $('<iframe>').attr({
                    name: target,
                    src: ''
                }),
                _this = this;

            // Ready ifrmae
            $iframe.one('load', function () {

                // respond response
                $iframe.on('load', function () {
                    var data;

                    try {
                        data = $(this).contents().find('body').text();
                    } catch (e) {
                        console.log(e.message);
                    }

                    if (data) {
                        try {
                            data = $.parseJSON(data);
                        } catch (e) {
                            console.log(e.message);
                        }

                        _this.submitDone(data);
                    } else {
                        _this.submitFail('Image upload failed!');
                    }

                    _this.submitEnd();

                });
            });

            this.$iframe = $iframe;
            this.$avatarForm.attr('target', target).after($iframe.hide());
        },

        click: function () {
            this.$avatarModal.modal('show');
            this.initPreview();
        },

        change: function () {
            var files,
                file;

            if (this.support.datauri) {
                files = this.$avatarInput.prop('files');

                if (files.length > 0) {
                    file = files[0];

                    if (this.isImageFile(file)) {
                        if (this.url) {
                            URL.revokeObjectURL(this.url); // Revoke the old one
                        }

                        this.url = URL.createObjectURL(file);
                        this.startCropper();
                    }
                }
            } else {
                file = this.$avatarInput.val();

                if (this.isImageFile(file)) {
                    this.syncUpload();
                }
            }
        },

        submit: function () {
            if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
                return false;
            }

            if (this.support.formData) {
                this.ajaxUpload();
                return false;
            }
        },

        rotate: function (e) {
            var data;

            if (this.active) {
                data = $(e.target).data();

                if (data.method) {
                    this.$img.cropper(data.method, data.option);
                }
            }
        },

        isImageFile: function (file) {
            if (file.type) {
                return /^image\/\w+$/.test(file.type);
            } else {
                return /\.(jpg|jpeg|png|gif)$/.test(file);
            }
        },

        startCropper: function () {
            var _this = this;

            if (this.active) {
                this.$img.cropper('replace', this.url);
            } else {
                this.$img = $('<img src="' + this.url + '">');
                this.$avatarWrapper.empty().html(this.$img);
                this.$img.cropper({
                    aspectRatio: 1,
                    preview: this.$avatarPreview.selector,
                    strict: false,
                    crop: function (data) {
                        var json = [
                            '{"x":' + data.x,
                            '"y":' + data.y,
                            '"height":' + data.height,
                            '"width":' + data.width,
                            '"rotate":' + data.rotate + '}'
                        ].join();

                        _this.$avatarData.val(json);
                    }
                });

                this.active = true;
            }
        },

        stopCropper: function () {
            if (this.active) {
                this.$img.cropper('destroy');
                this.$img.remove();
                this.active = false;
            }
        },

       

        syncUpload: function () {
            this.$avatarSave.click();
        },

        submitStart: function () {
            this.$loading.fadeIn();
        },

        submitDone: function (data) {
            if ($.isPlainObject(data)) {
                if (data.result) {
                    this.url = data.result;
                    if (this.support.datauri || this.uploaded) {
                        this.uploaded = false;
                        this.cropDone();
                    } else {
                        this.uploaded = true;
                        this.$avatarSrc.val(this.url);
                        this.startCropper();
                    }
                    this.$avatarInput.val('');
                } else if (data.message) {
                    this.alert(data.message);
                }
            } else {
                this.alert('Failed to response');
            }
        },

        submitFail: function (msg) {
            this.alert(msg);
        },

        submitEnd: function () {
            this.$loading.fadeOut();
        },

        cropDone: function () {
            this.$avatarForm.get(0).reset();
            this.$avatar.attr('src', this.url);
            this.stopCropper();
            this.$avatarModal.modal('hide');
        },

        
    };

    $(function () {
        return new CropAvatar($('#crop-avatar'));
    });

});