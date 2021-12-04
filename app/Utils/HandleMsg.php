<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-11-29
 * Time:    10:43
 * Email:   yxw770@gmail.com
 */


namespace App\Utils;

use App\Contracts\TgOPStepContract;
use App\Models\TgUser;
use App\Models\User;
use App\Models\UserInfos;
use App\Services\TgOPStepService;
use App\Services\UserOPService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

/**
 * 消息处理工具类
 *
 * Author:  Godfrey
 * Date:    2021-11-29
 * Time:    10:43
 * Email:   yxw770@gmail.com
 * Class HandleMsg
 * @package App\Utils
 */
class HandleMsg
{
    /**
     * 指令规则数组
     * @var string[]
     */
    protected static $rules = [
        'reg',//注册用户
        'exit',//退出步骤
        'resetEmail',//重置邮箱
        'resend',//重发邮件
    ];
    /**
     * 消息类型
     * 种类：0.未知 1.普通消息 2.指令类型 3.其他
     * @var int
     */
    protected static $msgType = 0;

    /**
     * 处理消息
     *
     * @param string $msg 消息内容
     * @param array $params 参数
     */
    public static function handleMsg($msg, $params)
    {

        //去除右边空格
        $msg = (string)Str::of($msg)->rtrim();

        /***************  正则匹配命令 start  ***************/
        //返回命令表达式
        $pattern = self::regexCommand();
        preg_match_all($pattern,
            $msg,
            $res, PREG_PATTERN_ORDER);
        if (empty($res[0])) {
            self::$msgType = 1;
        } else {
            self::$msgType = 2;
            unset($msg);

            if (!empty($res[1][0]) && $res[1][0] != "") {
                $msg['commend'] = $res[1][0];
            } else {
                $msg['commend'] = $res[2][0];
                $msg['text'] = $res[3][0];
            }
        }
        /***************  正则匹配命令 end  ***************/
        switch ($params['type']) {
            case 'telegram':
                $user = new UserOPService();
                $userData = $user->getUserByTg($params['tg_userid'], $params['bot_id']);
                return self::telegram($msg, $params, $userData);
            default:
                return false;
        }
    }

    /**
     * telegram消息处理
     *
     * @param $msg
     * @param $params
     */
    protected static function telegram($msg, $params, $userData)
    {
        $tgOPStep = new TgOPStepService();
        $msgData = [
            'token' => $params['token'],
            'chat_id' => $params['tg_userid'],
            'msg' => '',
            'message_id' => $params['message_id'],
        ];
        switch (self::$msgType) {
            case 1:
                //普通消息
                $botId = $params['bot_id'];
                $tgUserid = $params['tg_userid'];
                $step = $tgOPStep->isExist($tgUserid, 0, $botId, 5 * 60);
//                dd($step);
                if ($step['status']) {
                    $data = $step['data'];
                    //存在数据，找出步骤进行下一步操作

                    switch ($data['type']) {
                        case 1:
                            //注册
                            //校验是否为邮箱
                            if ($data['step'] == 0) {
                                //第一步，发送邮件
                                return self::tgRegSendEmail($msg, $userData, $params);
                            } elseif ($data['step'] == 1) {

                                if (strlen($msg) == 6 && (int)($msg) >= 100000) {
                                    //第二步，验证邮件验证码是否正确
                                    /**********  读取用户id   **********/

                                    if ($userData['status'] == 0) {
                                        $msgData['msg'] = "用户信息获取错误，请重新输入”/reg“指令重新注册";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);

                                    }
                                    $userData = $userData['data'];

                                    $email = $userData->email;

                                    $emailHelper = new Email();

                                    if ($emailHelper->verifyCode($email, $msg, 'regcode', $userData->id)) {
                                        $tgOPStep->setStep($tgUserid, 1, $botId, 5 * 60, 2);
                                        $user = User::find($userData->id);
                                        if ($user->state == 0) {
                                            $user->state = 1;
                                            $user->save();
                                            $msgData['msg'] = "验证成功，欢迎您的加入";
                                            self::sendMessageOfTg($msgData);
                                            return J(200, $msgData['msg']);
                                        }
                                        $msgData['msg'] = "验证成功，欢迎您的加入";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                    }
                                    $msgData['msg'] = "验证失败，原因：" . $emailHelper->getError();
                                    self::sendMessageOfTg($msgData);
                                    return J(200, $msgData['msg']);

                                }
                                $msgData['msg'] = "验证码长度不正确或格式错误";
                                self::sendMessageOfTg($msgData);
                                return J(200, $msgData['msg']);

                            }


                        default:
                            //未知
                            return false;
                    }
                }
                $msgData['msg'] = "普通消息";
                self::sendMessageOfTg($msgData);
                return J(200, $msgData['msg']);

            case 2:
                //指令消息
                $botId = $params['bot_id'];
                $tgUserid = $params['tg_userid'];

                switch ($msg['commend']) {
                    case '/reg':
                        //注册用户
                        //是否带邮箱

                        if ($userData['status'] == 1 && $userData['data']->state > 0) {
                            $msgData['msg'] = "您账户已绑定邮箱，如需换绑请输入“/resetEmail”";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        }
                        if (empty($msg['text'])) {
                            //不带则设置为注册模式步骤1，等待对方发送邮箱号
                            /********* 设置步骤模式 注册模式 第一步添加邮箱  *******/
                            $res = $tgOPStep->isExist($tgUserid, 1, $botId, 0);
                            if (!$res['status']) {
                                $msgData['msg'] = "请输入您的邮箱地址进行绑定";
                                self::sendMessageOfTg($msgData);
                                $tgOPStep->add($tgUserid, $params['msg_id'], 1, $botId, 5 * 60, 0);
                                return J(200, $msgData['msg']);
                            } else {
                                $data = $res['data'];
                                if ($data['step'] == 0) {
                                    $msgData['msg'] = "您输入的邮箱号不正常，请重新输入，退出注册模式请输入：“/exit”";
                                    self::sendMessageOfTg($msgData);
                                    return J(200, $msgData['msg']);

                                } elseif ($data['step'] == 1) {
                                    $msgData['msg'] = "您输入验证码不正确，请重新输入，退出注册模式请输入：“/exit”";
                                    self::sendMessageOfTg($msgData);
                                    return J(200, $msgData['msg']);


                                }
                            }
                        } else {
                            //含有邮箱注册条件
                            $res = $tgOPStep->isExist($tgUserid, 1, $botId, 0);
                            if ($res['status'] != 0) {
                                switch ($res['data']['step']) {
                                    case 0:
                                        //输入邮箱发送
                                        break;
                                    case 1:
                                        //输入验证码
                                        $msgData['msg'] = "您输入验证码不正确，请重新输入，退出注册模式请输入：“/exit”";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                    default:
                                        $msgData['msg'] = "您已完成注册，请勿重复操作";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                }

                            }
                            return self::tgRegSendEmail($msg['text'], $userData, $params);

                        }

                        return '你好，我是PHP程序！';
                        break;
                    case '/exit':
                        //退出当前步骤
                        $res = $tgOPStep->exitStep($tgUserid,$botId);
                        if($res >0){
                            $msgData['msg'] = "已关闭所有操作。";

                        }else{
                            $msgData['msg'] = "您没有可以关闭的操作。";
                        }
                        self::sendMessageOfTg($msgData);
                        return J(200, $msgData['msg']);
                }
                return;
            default:
                return false;
        }
    }


    /**
     * 指令消息正则替换
     *
     * @param $rules
     * @return string
     */
    protected static function regexCommand()
    {
        $exp = "/^(&&**)$|^(&&**) (.*)/";
        $rule_text = "";
        foreach (self::$rules as $k => $v) {
            if ($k == 0) {
                $rule_text .= "\/" . $v;

            } else {
                $rule_text .= "|\/" . $v;
            }
        }
        return Str::replaceArray('&&**', [$rule_text, $rule_text], $exp);
    }

    protected static function tgRegSendEmail($email, $userData, $params)
    {
        $msgData = [
            'token' => $params['token'],
            'chat_id' => $params['tg_userid'],
            'msg' => '',
            'message_id' => $params['message_id'],
        ];
        $botId = $params['bot_id'];
        $tgUserid = $params['tg_userid'];
        if (is_email($email)) {
            /************  校验用户是否已加入会员系统 start ************/

            if ($userData['status'] == 0) {
                //还未入库
                /************  注册用户 start ************/

                try {
                    DB::beginTransaction();
                    //添加用户表数据
                    $user = new User;
                    $user->email = $email;
                    $user->password = null;
                    $user->phone = null;
                    $user->state = 0;
                    $user->is_del = 0;
                    $user->save();
                    //添加telegram用户表数据
                    $tgUser = new TgUser;
                    $tgUser->userid = $user->id;
                    $tgUser->tg_userid = $tgUserid;
                    $tgUser->username = $params['username'];
                    $tgUser->first_name = $params['first_name'];
                    $tgUser->last_name = $params['last_name'];
                    $tgUser->bot_id = $botId;
                    $tgUser->save();
                    //添加用户信息
                    $userInfos = new UserInfos;
                    $userInfos->userid = $user->id;
                    $userInfos->tg_id = $tgUser->id;
                    $userInfos->save();
                    DB::commit();
                } catch (\Exception $e) {
                    Log::error($e->getMessage(), $e->getTrace());
                    DB::rollBack();
                    return false;
                }

                /************  注册用户  end  ************/
            } else {
                //已入库

                $userData = $userData['data'];
                $user = User::find($userData->id);

                if ($userData->state != 0) {
                    $msgData['msg'] = "您账户已绑定邮箱，如需换绑请输入“/resetEmail”";
                    self::sendMessageOfTg($msgData);
                    return J(200, $msgData['msg']);

                }
                if ($userData->email != $email) {
                    //未找到对应改的邮箱，代表更换了新的邮箱
                    //更换你邮箱信息
                    if (empty($user)) {
                        Log::error("用户id：" . $userData->id . "查找不到；操作信息id：" . $params['message_id']);
                        return false;
                    } else {
                        $user->email = $email;
                        $user->save();

                    }
                }

            }
            $userid = $user->id;
            /************  校验用户是否已加入会员系统  end  ************/

            /***************  发送注册验证码邮件 start ***************/
            $msgData['msg'] = "验证码已发送，请在5分钟内，回复邮件内的验证码，2分钟内未收到邮件，请回复“/resend”指令重发邮件，请注意您的垃圾邮件内是否存在验证码邮件！";
            self::sendMessageOfTg($msgData);
            $emailHelper = new Email();
            $res = $emailHelper->sendCode($email, $userid, 'regcode');
            if ($res) {
                //设置步数
                $tgOPStep = new TgOPStepService();
                if ($tgOPStep->setStep($tgUserid, 1, $botId, 5 * 60, 1)['status'] == 0) {
                    $tgOPStep->add($tgUserid, $params['msg_id'], 1, $botId, 5 * 60, 1);

                }
                return J(200, '已发送验证码到你的邮箱，请注意查收', 60);
            } else {
                $msgData['msg'] = "验证码发送失败，原因：" . $emailHelper->getError();
                self::sendMessageOfTg($msgData);
                return J(500, $emailHelper->getError());
            }
            /***************  发送注册验证码邮件 end ***************/
        } else {
            $msgData['msg'] = "您输入的邮箱号格式不正确，请重新输入，退出注册模式请输入：“/exit”";
            self::sendMessageOfTg($msgData);
            return J(200, $msgData['msg']);
        }
    }

    /**
     * * 发送消息.
     *
     * <code>
     * $params = [
     *   'token'                => '',
     *   'chat_id'              => '',
     *   'msg'                  => '',
     *   'message_id'           => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendmessage
     *
     * @param array $params [
     *
     * @var string $token 必填. 机器人accessToken
     * @var int|string $chat_id 必填. telegram 用户id
     * @var string $msg 必填. 发送的消息
     * @var int $message_id 必填，消息id
     *
     * ]
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    protected static function sendMessageOfTg($params)
    {

        $tgBot = new Api($params['token']);
        $params = [
            'chat_id' => $params['chat_id'],
            'text' => $params['msg'],
            'disable_web_page_preview' => true,
            'disable_notification' => false,
            'reply_to_message_id' => $params['message_id'],
        ];

        $tgBot->sendMessage($params);
//        dd(123);
    }
}
