<?php


namespace App\Utils;

use App\Models\EmailCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * 邮箱服务
 *
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    17:19
 * Email:   yxw770@gmail.com
 * Class Email
 * @package app\common\util
 */
class Email
{
    protected $error;
    protected $expire_in = 300;

    /**
     * 获取错误信息
     */
    public function getError()
    {
        return $this->error ? $this->error : '';
    }

    /**
     * 设置超时秒数
     */
    public function setExpireIn($expire_in)
    {
        return $this->expire_in = $expire_in;
    }

    /**
     * 发送验证码
     */
    public function sendCode($email, $userid, $screen = '')
    {
        // 检测是否存在验证码（5分钟）
        $expire_time = $_SERVER['REQUEST_TIME'] - $this->expire_in;
        $where = [
            ['email', '=', $email],
            ['create_at', '>', $expire_time],
            ['screen', '=', $screen],
            ['userid', '=', $userid]
        ];
        $email_code = EmailCode::where($where)->first();
        if (!empty($email_code)) {
            $this->error = '您的操作太频繁，请稍后再试！';
            return false;
        }
        if (!$this->emailCheckError($email)){
            return false;
        }
        // 生成验证码
        $code = (string)(mt_rand(100000, 999999));
        $time = time();

        $data = [
            'code' => $code,
            'zhTime' => date("Y-m-d H:i:s", $time + 5 * 60),
            'enTime' => dateTimeChangeByZone(date("m/d/Y H:i:s", $time + 5 * 60), 'Asia/Shanghai', 'UTC', 'm/d/Y H:i:s')
        ];
        Mail::send('mail.regcode' , $data, function ($message) use ($screen, $email) {
            switch ($screen) {
                case 'regcode':
                    $subject = "注册验证码-Register verification Code";
                    break;
                default:
                    $subject = "系统邮件-System email";
                    break;
            }
            $message->to((string)$email)->subject($subject);
        });

        if (!(count(Mail::failures()) < 1)) {
            $this->error = '验证码发送失败！';
            return false;
        }
        $emailCode = new EmailCode;
        $emailCode->userid = $userid;
        $emailCode->email = $email;
        $emailCode->code = $code;
        $emailCode->screen = $screen;
        $emailCode->status = 0;
        $emailCode->create_at = $time;
        if ($emailCode->save()) {
            return true;
        } else {
            $this->error = '验证码发送失败！';
            return false;
        }
    }

    /**
     * 验证短信验证码
     * @param string $email 邮件
     * @param string $code 验证码
     * @param string $screen 验证场景（可选）
     * @param int    $userid  用户id
     * @return boolean        验证情况
     */
    public function verifyCode($email, $code, $screen = '',$userid)
    {
        if (!$this->emailCheckError($email)){
            return false;
        }
        // 检测是否存在验证码（5分钟）
        $expire_time = $_SERVER['REQUEST_TIME'] - $this->expire_in;
        $where=[
            ['email','=',$email],
            ['code','=',$code],
            ['screen','=',$screen],
            ['userid','=',$userid]
        ];
        $emailCode = DB::table('email_code')->where($where)->orderByDesc('id')->first();
        $flag = true;
        if (!empty($emailCode)) {
            if ($emailCode['status'] == 1) {
                $this->error = '该验证码已失效，请重新获取！';
                $flag = false;
            }
            if ($emailCode['create_at'] <= $expire_time) {
                $this->error = '该验证码已超时，请重新获取！';
                $flag = false;
            }
            DB::table('email_code')->where('id', $emailCode['id'])->update(['status' => 1]);
        } else {
            $this->error = '验证码错误！';
            $flag = false;
        }
        if (FALSE === $flag) {
            $plog['email'] = $email;
            $plog['code'] = $code;
            $plog['screen'] = $screen;
            $plog['type'] = 1;
            $plog['userid'] = $userid;
            $plog['ctime'] = time();
            DB::table('email_code')->insert($plog);
        } else {
            return true;
        }
    }


    protected function emailCheckError($email)
    {
        if (sysconf('email_error_limit') > 0 && sysconf('email_error_time') > 0) {
            $start_time = time() - sysconf('email_error_time') * 60;
            $where = [
                ['ctime', '>', $start_time],
                ['email', '=', $email]
            ];
            $count = DB::table("verify_email_error_log")->where($where)->count("id");
            if ($count >= sysconf('email_error_limit')) {
                $last_time = DB::table('verify_email_error_log')->where('email', $email)->orderByDesc('id')->limit(1)->value('ctime');
                if ($last_time > 0) {
                    $time = $last_time + sysconf('email_error_time') * 60 - time();
                    $time_str = sec2Time($time);
                    $this->error = '输入错误验证码超限，请' . $time_str . '后重新验证!';
                    return false;
                }
            }
        }
        return true;
    }
}

