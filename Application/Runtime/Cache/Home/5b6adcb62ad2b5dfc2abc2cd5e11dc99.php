<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>登录</title>

    <!--<link href="/Feasims/Public/css/assets/css/index_font.css" rel="stylesheet" type="text/css"/>-->
    <!--<link rel="stylesheet" href="__PUBLIC_/css/font/iconfont.css" >-->
    <style>
        body {
            color: #fff;
            font-family: "微软雅黑";
            font-size: 14px;
        }

        .wrap1 {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            margin: auto
        }

        /*把整个屏幕真正撑开--而且能自己实现居中*/
        .main_content {
            background: url('/Feasims/Public/img/main_bg.png') repeat;
            background-color: black;
            opacity: 0.8;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
            float: none;
            border-radius: 8px;
        }

        .form-group {
            position: relative;
        }

        .login_btn {
            display: block;
            background: #3872f6;
            color: #fff;
            font-size: 15px;
            width: 100%;
            line-height: 50px;
            border-radius: 3px;
            border: none;
            margin-top: 20px;
        }

        .login_input {
            width: 100%;
            height:45px;
            border: 1px solid #3872f6;
            border-radius: 3px;
            line-height: 40px;
            padding: 2px 5px 2px 30px;
            background: none;
        }
        .login_yz {
            width: 60%;
            height: 45px;
            border: 1px solid #3872f6;
            border-radius: 3px;
            line-height: 40px;
            padding: 2px 5px 2px 30px;
            background: none;
        }
        .icon_font {
            position: absolute;
            bottom: 10px;
            left: 3px;
            font-size: 18px;
            color: #3872f6;
        }


        .font16 {
            font-size: 16px;
        }

        .mg-t20 {
            margin-top: 20px;
        }

        @media (min-width: 200px) {
            .pd-xs-20 {
                padding: 20px;
            }
        }

        @media (min-width: 768px) {
            .pd-sm-50 {
                padding: 50px;
            }
        }

        #grad {
            background: -webkit-linear-gradient(#4990c1, #52a3d2, #6186a3); /* Safari 5.1 - 6.0 */
            background: -o-linear-gradient(#4990c1, #52a3d2, #6186a3); /* Opera 11.1 - 12.0 */
            background: -moz-linear-gradient(#4990c1, #52a3d2, #6186a3); /* Firefox 3.6 - 15 */
            background: linear-gradient(#4990c1, #52a3d2, #6186a3); /* 标准的语法 */
        }
    </style>

</head>
<body style="background:url('/Feasims/Public/img/loginBackground.jpg') no-repeat">

<div class="container wrap1" style="margin-top: 50px;width: 660px">
    <div>
        <img src="/Feasims/Public/img/loginLogo.png" alt="" style="margin-top: 10px;margin-left: 20px;position: relative"><span style="font-size: 24px;position: absolute;top: 20px;left: 170px;font-family: SimSun">中国电信嘉兴分公司网站不良信息检测系统</span>
    </div>
    <h2 class="mg-b20 text-center" style="text-align: center;"></h2>
    <div class="col-sm-8 col-md-5 center-auto pd-sm-50 pd-xs-20 main_content" style="width: 400px" >
        <p class="text-center font16">用户登录</p>
        <form  >
            <div class="form-group mg-t20">
                <!--<input type="text" class="login_input" id="username" name="username" placeholder="请输入用户名" style="color: white" pattern="^[a-zA-Z]{1}[a-zA-Z0-9]{3,11}$"  title="请输入4-12位字母或数字,首位必须为英文字母" />-->
                <input type="text" class="login_input" id="username" name="username" placeholder="请输入用户名" style="color: white" />
            </div>
            <div class="form-group mg-t20">
                <!--<input type="password" class="login_input" id="password" name="password" placeholder="请输入密码" style="color: white" pattern="^[\da-zA-Z.!@#$%^&*]{6,12}$"  title="请输入6-12位必须包含字母数字特殊字符" required />-->
                <input type="password" class="login_input" id="password" name="password" placeholder="请输入密码" style="color: white"  required />
            </div>
            <div class="form-group mg-t20">
                <input type="text" name="code" class="login_yz" id="code" placeholder="请输入验证码" style="color: white"/>
                <span><img src="<?php echo U('Login/verify');?>" class="verifyimg reloadverify"
                style="vertical-align:middle; cursor: pointer;margin-top:-5px;" alt="验证码" id="code"
                align="bottom" title="看不清可单击图片刷新" 

                onclick="this.src='<?php echo U('Login/verify');?>?d='+Math.random();"></span>
                <font></font>
            </div>
            <submit type="button" class="login_btn" id="submit" style="text-align: center">登 录</submit>
        </form>
    </div><!--row end-->
</div>



</body>
</html>
<!--[if !IE]> -->

<script type="text/javascript">
    window.jQuery || document.write("<script src='/Feasims/Public/lib/js/jquery.min.js'>" + "<" + "/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='/Feasims/Public/css/assets/js/jquery-1.10.2.min.js'>" + "<" + "/script>");
</script>
<![endif]-->
<script type="text/javascript">
    $(document).ready(function () {
        document.onkeydown = function (e) {
            var ev = document.all ? window.event : e;
            if (ev.keyCode == 13) {
                $('#f_submit').trigger("click");
            }
        };
    });
    $('#submit').click(function () {
        //alert($("input[name!='username']").val());
        if ($("input[name='username']").val() == '' || $("input[name='password']").val() == '') {
            alert('用户名或者密码不能为空');
            return false;
        }
        //var data = $('#submit').serialize();
        var username = $('#username').val();
        var password = $('#password').val();
        var reguPass =/^[0-9A-Za-z_]{6,12}$/;
        var reguName =/^[a-zA-Z]{1}[a-zA-Z0-9]{3,11}$/;

        var reName = new RegExp(reguName);
        var rePass = new RegExp(reguPass);
        if(!reName.test(username)){
            alert('用户名请输入4-12位字母或数字,首位必须为英文字母')
            return;
        }else if(!rePass.test(password)){
          alert('密码请输入6-12位必须包含字母数字特殊字符')
            return;
        }
        var code = $('#code').val();
        data = {'username':username,'password':password,'code':code};
        //console.log(data);
        $.ajax({
            url: '/Feasims/index.php/Home/Login/index',
            type: 'get',
            async: false,
            dataType:'json',
            data:data,
            success: function (data) {
                if (data.status == 'success') {
                    //window.location.href = "<?php echo U('Index/index');?>";
                    window.location.href = "<?php echo U('Text/text_first_trial');?>";
                } else if (data.status == 'error0') {
                    alert('验证码不能为空！');
                } else if (data.status == 'error1') {
                    alert('验证码已过期！');
                } else if (data.status == 'error2') {
                    alert('验证码不正确！');
                } else if (data.status == 'error') {
                    alert('账号或密码错误');
                    $('#code').attr('src', "<?php echo U('Login/verify');?>?d='+Math.random()");

                } else if (data.status == 'warnning') {
                    alert('您的密码过于简单，请及时修改！');
                    //window.location.href = "<?php echo U('Index/index');?>";
                    window.location.href = "<?php echo U('Text/text_first_trial');?>";

                } else {
                    alert('当前验证码已失效，请刷新验证码！');
                }

            }
        });
    });
   // $('#province').text("<?php echo (C("PROVINCE")); ?>");
</script>