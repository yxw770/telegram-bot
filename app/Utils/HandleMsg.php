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
        'email',//邮箱
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
        $msg = Str::of($msg)->rtrim();

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
                $msg['text'] = $res[2][0];
            }
        }
        /***************  正则匹配命令 end  ***************/
        switch ($params['type']) {
            case 'telegram':
                return self::telegram($msg, $params);
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
    protected static function telegram($msg, $params,TgOPStepContract $tgOPStep)
    {

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

                return "普通";
            case 2:
                //指令消息
                $botId = $params['bot_id'];
                $tgUserid = $params['tg_userid'];
                switch ($msg['commend']) {
                    case '/reg':
                        //注册用户
                        //是否带邮箱
                        if (empty($msg['text'])) {
                            //不带则设置为注册模式步骤1，等待对方发送邮箱号
                            $msgData['msg'] = "请发送您要注册的邮箱号";

                            self::sendMessageOfTg($msgData);
                            $tgOPStep->add();
                        } else {
                            //
                            $time = time();
                            $data = [
                                'code' => 156841,
                                'zhTime' => date("Y-m-d H:i:s", $time + 5 * 60),
                                'enTime' => dateTimeChangeByZone(date("m/d/Y H:i:s", $time + 5 * 60), 'Asia/Shanghai', 'UTC', 'm/d/Y H:i:s')
                            ];
                            Mail::send('mail.vercode', $data, function ($message) {
                                $to = 'yxw770@gmail.com';
                                $message->to($to)->subject('注册验证码-Register verification Code');
                            });

                            if (count(Mail::failures()) < 1) {
                                echo '发送邮件成功，请查收！';
                            } else {
                                echo '发送邮件失败，请重试！';
                            }
                        }

                        return '你好，我是PHP程序！';
                        break;
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

    }
}
