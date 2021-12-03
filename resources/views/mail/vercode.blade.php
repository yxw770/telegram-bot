<style>
    .qmbox * {
        margin: 0;
        font-family: Helvetica;
        box-sizing: border-box;
    }

    .qmbox a {
        text-decoration: none;
        background-color: transparent;
        outline: none;
        cursor: pointer;
    }

    .qmbox html.ar {
        direction: rtl;
    }

    .qmbox html.ar input {
        text-align: right;
    }

    .bg {
        /*                background-color:#E4E4E4;*/
    }
</style>


<div class="bg" style="">

    <div style="width:100%;max-width: 550px; padding: 0 20px;    margin: 0 auto;    ">

        <div style="box-shadow: 0px 3px 10px #ccc;">
            <div
                style="max-width:100%;background-color: #f1f1f1; padding: 20px 16px; font-weight: bold;font-size: 20px;color: rgb(22,24, 35);border-top: 4px solid #FA0F00;">
                注册身份验证码
            </div>
            <div
                style="max-width:100%;background-color: #f8f8f8; padding: 24px 16px;font-size: 17px;color: rgba(22,24, 35, 0.75);line-height: 20px;">
                <p style="margin-bottom:20px;">请把下面一串代码发送给机器人，用于验证您的账号：</p>
                <p style="border: solid 1px #cccccc61;background: #eee;font-size: 48px;text-align: center;font-weight: bold;padding-top: 20px;padding-bottom: 20px;margin: 36px 15%;">
                    {{$code}}</p>
                <p style="margin-bottom:20px;">验证码将过期于北京时间：<span
                        style="font-weight: bold;color:#FA0F00;">{{$zhTime}}</span> 。</p>
                <p style="margin-bottom:20px;">如果您没有请求此代码，则可以忽略此消息。</p>

            </div>
        </div>
        <div style="box-shadow: 0px 3px 10px #ccc;margin-top: 20px;">
            <div
                style="max-width:100%;background-color: #f1f1f1; padding: 20px 16px; font-weight: bold;font-size: 20px;color: rgb(22,24, 35);border-top: 4px solid #4e83f9;">
                Register verification Code
            </div>
            <div
                style="max-width:100%;padding: 40px 16px 20px;font-size: 15px;color: rgba(22, 24, 35, 0.5);line-height:18px;background-color: #f8f8f8;">
                <p style="margin-bottom:20px;">To verify your account, send this code to Bot:</p>
                <p style="border: solid 1px #cccccc61;background: #eee;font-size: 48px;text-align: center;font-weight: bold;padding-top: 20px;padding-bottom: 20px;margin: 36px 15%;">
                    {{$code}}</p>
                <p style="margin-bottom:20px;">Verification codes expire after <span
                        style="font-weight: bold;color:#FA0F00;">{{$enTime}} </span>UTC.</p>
                <p style="margin-bottom:20px;">If you didn't request this code, you can ignore this message.</p>
            </div>
        </div>
    </div>
    <div></div>

    <style type="text/css">
        .qmbox style,
        .qmbox script,
        .qmbox head,
        .qmbox link,
        .qmbox meta {
            display: none !important;
        }
    </style>
</div>
