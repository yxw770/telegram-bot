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
use App\Models\BotCommand;
use App\Models\TgSendMsg;
use App\Models\TgUser;
use App\Models\User;
use App\Models\UserInfos;
use App\Models\VipGroup;
use App\Services\ServerChanService;
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
        $pattern = "/^(\/[A-Za-z0-9]{1,20})$|^(\/[A-Za-z0-9]{1,20}) (.*)/";
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
        $msgData = [
            'token' => $params['token'],
            'chat_id' => $params['tg_userid'],
            'msg' => '',
            'message_id' => $params['message_id'],
            'trigger_msg' => json_encode($msg),
            'bot_id' => $params['bot_id'],
        ];

        $tgOPStep = new TgOPStepService();

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
                        //注册模式
                        case 1:
                            //注册
                            //校验是否为邮箱
                            if ($data['step'] == 0) {
                                //第一步，发送邮件
//                                return true;
                                return self::tgRegSendEmail($msg, $userData, $params, $msgData);
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
                                        $userInfos = UserInfos::where("userid", $user->id)->first();
                                        if ($user->state == 0) {
                                            $user->state = 1;
                                            $user->save();
                                            $userInfos->vip_group = sysconf("tg_default_vip_group_id");
                                            $userInfos->save();
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

                        //换绑模式
                        case 2:
                            if ($data['step'] == 1) {
                                if (strlen($msg) == 6 && (int)($msg) >= 100000) {
                                    //第二步，验证邮件验证码是否正确
                                    /**********  读取用户id   **********/
                                    if ($userData['status'] == 0) {
                                        $msgData['msg'] = "用户信息获取错误，请重新输入”/reg“指令重新注册";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                    }
                                    $userData = $userData['data'];
                                    $email = $data['params']['email'];
                                    $emailHelper = new Email();
                                    if ($emailHelper->verifyCode($email, $msg, 'resetcode', $userData->id)) {
                                        $tgOPStep->setStep($tgUserid, 2, $botId, 0, 2);
                                        $user = User::find($userData->id);
                                        $user->email = $email;
                                        $user->save();
                                        $msgData['msg'] = "换绑成功，新绑定的邮箱为：" . $email;
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
//                            return true;
                    }
                }
                $msgData['msg'] = "普通消息";
                self::sendMessageOfTg($msgData);
                return J(200, $msgData['msg']);

            case 2:
                /***********  校验用户权限  start  ***********/
                $userid = $userData['status'] == 1 ? ($userData['data']->state == 1 ? $userData['data']->id : 0) : 0;
                if ($userData['status'] == 1 && $userData['data']->state == 2) {
                    //用户冻结，无法操作任何东西
                    $msgData['msg'] = "您账户已被冻结，请联系客服处理。";
                    self::sendMessageOfTg($msgData);
                    return J(200, $msgData['msg']);
                }
                $res = self::commandAuthCheck($userid, $msg['commend']);
                if ($res['status'] != 1) {
                    $msgData['msg'] = $res['msg'];
                    self::sendMessageOfTg($msgData);
                    return J(200, $msgData['msg']);
                }
                /***********  校验用户权限   end   ***********/
                //指令消息
                $botId = $params['bot_id'];
                $tgUserid = $params['tg_userid'];

                switch ($msg['commend']) {
                    //说明书
                    case '/help':

                    //注册
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
                            return self::tgRegSendEmail($msg['text'], $userData, $params, $msgData);

                        }

                        return '你好，我是PHP程序！';
                        break;

                    //关闭所有步骤
                    case '/exit':
                        //退出当前步骤
                        $res = $tgOPStep->exitStep($tgUserid, $botId);
                        if ($res > 0) {
                            $msgData['msg'] = "已关闭所有操作。";

                        } else {
                            $msgData['msg'] = "您没有可以关闭的操作。";
                        }
                        self::sendMessageOfTg($msgData);
                        return J(200, $msgData['msg']);

                    //重发邮件
                    case '/resend':
                        //重新发送邮件
                        if (!($userData['status'] == 1 && $userData['data']->state > 0)) {
                            //未注册，发送注册验证码
                            $res = $tgOPStep->isExist($tgUserid, 1, $botId, 0, 1);
                            if (!$res['status']) {
                                //不在注册步骤内
                                $msgData['msg'] = "未知指令";
                                self::sendMessageOfTg($msgData);
                                return J(200, $msgData['msg']);
                            }
                            $email = $userData['data']->email;
                            return self::tgRegSendEmail($email, $userData, $params, $msgData);
                        } else {
                            //已注册发送换绑验证码
                            $res = $tgOPStep->isExist($tgUserid, 2, $botId, 0, 1);
                            if (!$res['status']) {
                                //不在换绑步骤内
                                $msgData['msg'] = "未知指令";
                                self::sendMessageOfTg($msgData);
                                return J(200, $msgData['msg']);
                            }
                            $email = $userData['data']->email;
                            return self::tgResetSendEmail($res['data']['params']['email'], $userData, $params, $msgData);
                        }


                    //用户信息
                    case "/getme":
                        $data = $userData['data'];
                        $group = UserInfos::where("userid", $userid)->first();

                        if (is_null($group)) {
                            $vip = "未知组别";
                        }
                        if ($group->vip_expire_at == -1 || $group->vip_expire_at > time()) {
                            $group_id = $group->vip_group;
                        } else {
                            $group_id = sysconf("tg_default_vip_group_id");
                        }
                        $vip_group = VipGroup::where("id", $group_id)->where("is_del", 0)->first();
                        if (empty($vip_group)) {
                            $vip = "未知组别";
                        } else {
                            $vip = $vip_group->name;
                        }
                        $ex_time = $group->vip_expire_at == -1 ? '永不到期' : date("Y-m-d H:i:s", $group->vip_expire_at);
                        $msgData['msg'] = unicodeDecode("\ud83d\udc64 ") . "用户编号：" . $data->id . "\n" . unicodeDecode("\ud83d\udce8") . ' 邮箱号： ' . $data->email .
                            "\n" . unicodeDecode("\ud83d\udc51") . " VIP组别：$vip" . "\n" . unicodeDecode("\ud83d\udd58") . " VIP到期时间：" . $ex_time;
                        self::sendMessageOfTg($msgData);
                        return J(200, $msgData['msg']);

                    //绑定server酱sendKey
                    case "/bindServerChan":
                        if (empty($msg['text'])) {
                            $msgData['msg'] = "格式有误，请输入“/bindServerChan 您的SendKey”\n例如：/bindServerChan SCT100421T8tGLeWnDF6ZwMF2BRCr8DbEZ";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        }
                        $serverChan = new ServerChanService();
                        if ($serverChan->setSendKey($userData['data']->id, $msg['text'])) {
                            $msgData['msg'] = "Server酱SendKey绑定成功！";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        } else {
                            $msgData['msg'] = "Server酱SendKey绑定失败，请联系技术人员处理";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        }

                    //换绑邮箱
                    case "/resetEmail":
                        if (!($userData['status'] == 1 && $userData['data']->state > 0)) {
                            $msgData['msg'] = "您账户还未注册，如需注册请输入“/reg”";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        }
                        if (!empty($msg['text'])) {
                            //含有邮箱注册条件
                            $res = $tgOPStep->isExist($tgUserid, 2, $botId, 0);
                            if ($res['status'] != 0) {
                                switch ($res['data']['step']) {
                                    case 1:
                                        //输入验证码
                                        $msgData['msg'] = "您输入验证码不正确，请重新输入，退出换绑邮箱模式请输入：“/exit”";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                    default:
                                        $msgData['msg'] = "您已完成换绑操作，请勿重复操作";
                                        self::sendMessageOfTg($msgData);
                                        return J(200, $msgData['msg']);
                                }

                            }
                            return self::tgResetSendEmail($msg['text'], $userData, $params, $msgData);

                        } else {
                            $msgData['msg'] = "格式有误，请输入“/resetEmail 您要换绑的email”\n例如：/resetEmail xxx@gmail.com";
                            self::sendMessageOfTg($msgData);
                            return J(200, $msgData['msg']);
                        }
                }
                return true;
            default:
                return true;
        }
    }


    /***
     * @Name 指令权限 认证
     * @Author Godfrey<yxw770@gmail.com>
     * @Date 2021-12-06 12:01
     * @Param int $userid 用户ID
     * @param string $command 指令
     * @Return array
     **/
    protected static function commandAuthCheck($userid, $command)
    {

        $commands = BotCommand::where("command", $command)->first();
        if (empty($commands)) {
            //指令不存在
            return [
                'status' => 0,
                'msg' => '未知指令',
            ];
        }
        if ($userid != 0) {
            //已创建用户
            /**************  读取用户会员组 start  **************/
            $group = UserInfos::where("userid", $userid)->first();

            if (is_null($group)) {
                return [
                    'status' => 0,
                    'msg' => '用户会员分组异常，请联系技术处理，用户ID：' . $userid,
                ];
            }
            if ($group->vip_expire_at == -1 || $group->vip_expire_at > time()) {
                $group_id = $group->vip_group;
            } else {
                $group_id = sysconf("tg_default_vip_group_id");
            }
            $vip_group = VipGroup::where("id", $group_id)->where("is_del", 0)->first();
            if (empty($vip_group)) {
                return [
                    'status' => 0,
                    'msg' => 'VIP分组不存在，请联系技术处理，用户ID:' . $userid,
                ];
            }
            $commandList = $vip_group->command_list;
            /**************  读取用户会员组  end   **************/

        } else {
            //游客模式指令
            $vip_group = VipGroup::where("id", 0)->where("is_del", 0)->first();
            if (empty($vip_group)) {
                return [
                    'status' => 0,
                    'msg' => 'VIP分组不存在，请联系技术处理.',
                ];
            }
            $commandList = $vip_group->command_list;
        }
        //判断该指令是否在指令集
        if (in_array($commands->id, $commandList)) {
            return [
                'status' => 1,
            ];
        } else {
            return [
                'status' => 0,
                'msg' => '您的权限不足，无法使用该指令.',
            ];
        }
    }

    /**
     * @Name telegram注册邮件发送
     * @Author Godfrey<yxw770@gmail.com>
     * @Date 2021-12-06 15:32
     * @Param $email    邮箱号
     * @param $userData 用户信息
     * @param $params   传入参数
     * @param $msgData  发送消息基本参数
     * @Return mixed
     **/
    protected static function tgRegSendEmail($email, $userData, $params, $msgData)
    {

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

            $msgData['msg'] = "验证码已加入发送队列，请稍等。如1分钟内未提示发送成功请重新注册。";
            self::sendMessageOfTg($msgData);
            $emailHelper = new Email();
            $res = $emailHelper->sendCode($email, $userid, 'regcode');
            if ($res) {
                //设置步数
                $tgOPStep = new TgOPStepService();
                if ($tgOPStep->setStep($tgUserid, 1, $botId, 5 * 60, 1)['status'] == 0) {
                    $tgOPStep->add($tgUserid, $params['msg_id'], 1, $botId, 5 * 60, 1);
                }
                $msgData['msg'] = "验证码发送成功，请在5分钟内，回复邮件内的验证码，2分钟内未收到邮件，请回复“/resend”指令重发邮件，请注意您的垃圾邮件内是否存在验证码邮件！";
                self::sendMessageOfTg($msgData);
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
     * @Name telegram换绑邮件发送
     * @Author Godfrey<yxw770@gmail.com>
     * @Date 2021-12-06 15:32
     * @Param $email    邮箱号
     * @param $userData 用户信息
     * @param $params   传入参数
     * @param $msgData  发送消息基本参数
     * @Return mixed
     **/
    protected static function tgResetSendEmail($email, $userData, $params, $msgData)
    {

        $botId = $params['bot_id'];
        $tgUserid = $params['tg_userid'];
        if (is_email($email)) {

            $userData = $userData['data'];

            $user = User::find($userData->id);

            if ($userData->state != 1) {
                $msgData['msg'] = "您账户状态无法进行换绑做错";
                self::sendMessageOfTg($msgData);
                return J(200, $msgData['msg']);
            }

            if ($userData->email == $email) {
                $msgData['msg'] = "您的换绑的邮箱与已绑邮箱相同！";
                self::sendMessageOfTg($msgData);
                return J(200, $msgData['msg']);
            }
            $userid = $user->id;
            $msgData['msg'] = "验证码已加入发送队列，请稍等。如1分钟内未提示发送成功请重新绑定。";
            self::sendMessageOfTg($msgData);
            $emailHelper = new Email();
            $res = $emailHelper->sendCode($email, $userid, 'resetcode');
            if ($res) {
                //设置步数
                $tgOPStep = new TgOPStepService();
                if ($tgOPStep->setStep($tgUserid, 2, $botId, 5 * 60, 1)['status'] == 0) {
                    $tgOPStep->add($tgUserid, $params['msg_id'], 2, $botId, 5 * 60, 1,['email'=>$email]);
                }
                $msgData['msg'] = "验证码发送成功，请在5分钟内，回复邮件内的验证码，2分钟内未收到邮件，请回复“/resend”指令重发邮件，请注意您的垃圾邮件内是否存在验证码邮件！";
                self::sendMessageOfTg($msgData);
                return J(200, '已发送验证码到你的邮箱，请注意查收', 60);
            } else {
                $msgData['msg'] = "验证码发送失败，原因：" . $emailHelper->getError();
                self::sendMessageOfTg($msgData);
                return J(500, $emailHelper->getError());
            }
        }else{
            $msgData['msg'] = "邮箱格式不正确！";
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
        $params1 = [
            'chat_id' => $params['chat_id'],
            'text' => $params['msg'],
            'disable_web_page_preview' => true,
            'disable_notification' => false,
            'reply_to_message_id' => $params['message_id'],
        ];

        $tgBot->sendMessage($params1);
        $data = [
            'tg_userid' => $params['chat_id'],
            'send_msg' => $params['msg'],
            'trigger_msg' => $params['trigger_msg'],
            'message_id' => $params['message_id'],
            'create_at' => time(),
            'bot_id' => $params['bot_id']
        ];
        TgSendMsg::insert($data);
//        dd(123);
    }
}
