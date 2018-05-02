/**
 * 删除操作
 * @param $url
 * ajax请求url
 * @param $id
 * 要删除的id
 * @param $_obj
 * 当前对象
 */
function deleteAlert($url,$id,$_obj) {
    swal({
        title: '确定要删除？',
        text: "真的真的真的要删除它吗",
        icon: 'warning',
        buttons: {
            delete: '删除',
            cancel: '取消'
        }
    }).then((value) => {
        switch (value) {
            case 'delete':
                $.ajax({
                    url: $url,
                    data: {'id':$id},
                    type: 'post',
                    dataType: 'json',
                    success: function (info) {
                        if (info.flag === true) {
                            swal("删除成功！", info.msg, "success").then(function () {
                                deleteOneTr($_obj);
                            });
                        } else {
                            swal("WTF", info.msg, "error");
                        }
                    },
                    error: function () {
                        swal("WTF", "删除失败了", "error");
                    }
                });
            case 'cancel':
                swal.close();
        }
    });
}

/**
 * 删除某一行
 * @param $_obj
 */
function deleteOneTr($_obj) {
    $_obj.closest('tr').remove();
}

/**
 * 登入验证的js
 */
function validateLogin(url) {
    $("#form").validator({

        fields: {
            //name 字段使用对象传参
            phone: {
                rule: "手机号: required; mobile",
                msg: {
                    required: "亲,这个框框是必填的哦",
                    mobile: "请输入11位中国地区号码",
                },
                ok: "",
                timely: 3,
                target: "#msg_phone",
            },
            password: {
                rule: "密码: required; length(6~32)",
                msg: {
                    required: "亲,这个框框是必填的哦",
                    length: "密码是6到32位之间哦",
                },
                ok: "",
                timely: 3,
                target: "#msg_pw",
            }

        },
        valid: function (form) {
            let me = this;
            me.holdSubmit();
            $.ajax({
                url: url,
                data: $(form).serialize(),
                type: "POST",
                success: function (info) {
                    if (info.flag === true) {
                        swal("验证通过！", info.msg, "success").then(function () {
                            me.holdSubmit(false);
                            form.submit()
                        });
                    } else {
                        me.holdSubmit(false);
                        swal("WTF,验证失败了?!!", info.msg, "error");
                    }
                }
            });
        }
    });
}

/**
 * 注册时的耐撕验证
 * @param url
 * 后端文件验证
 * @param url_for_phone
 * 检测手机号是否存在
 */
function validateRegister(url, url_for_phone) {

    $("#user").validator({

        fields: {
            //name 字段使用对象传参
            phone: {
                rule: "手机号: required; mobile;remote(" + url_for_phone + ")",
                msg: {
                    required: "亲,这个框框是必填的哦",
                    mobile: "请输入11位中国地区号码",
                },
                target: "#msg_phone",
            },
            username: {
                rule: "用户名: required",
                msg: {
                    required: "亲,给自己起一个帅气的名字吧",
                },
                target: "#msg_username",
            },

            email: "required; email",

            password: {
                rule: "密码: required; length(6~32)",
                msg: {
                    required: "亲,不填密码我怎么保护你?!",
                    length: "密码是6到32位之间哦",
                },
                target: "#msg_password",
            },
            confirm_password: {
                rule: "required; match(password)",
                msg: {
                    required: "亲,不填密码我怎么保护你",
                    match: "两次密码不一致",
                },
                timely: 3,
                target: "#msg_cpwd",
            },

        },
        valid: function (form) {
            let me = this;
            me.holdSubmit();
            $.ajax({
                url: url,
                data: $(form).serialize(),
                type: "POST",
                success: function (info) {
                    if (info.flag === true) {
                        swal("验证通过！", info.msg, "success").then(function () {
                            me.holdSubmit(false);
                            window.location.href = info.url;
                        });
                    } else {
                        me.holdSubmit(false);
                        swal("WTF,验证失败了?!!", info.msg, "error");
                    }
                }
            });
        }
    });
}

/**
 * 表单自动填充
 * @param data
 * 需要填充的数据(json格式)
 */
function popInput(data) {
    if (data=== false) {
        return;
    }
    // TODO 还要兼容多种情况
    const target = $('body').find('input,select,textarea');
    const info = $.parseJSON(data);
    $.each(info, function (index, value) {
        target.each(function () {
            let temp = $(this);
            if (index === temp.attr('name')) {
                temp.val(value).trigger('change');
            }
        })
    })
}

/**
 * 添加或编辑成功时的提示
 */
function info(flag) {
    if (flag === 'create_success') {
        toastr.success('添加成功');
    }else if(flag === 'update_success') {
        toastr.success('编辑成功');
    }

}
