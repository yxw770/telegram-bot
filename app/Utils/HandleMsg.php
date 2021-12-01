<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-11-29
 * Time:    10:43
 * Email:   yxw770@gmail.com
 */


namespace App\Utils;

use Illuminate\Support\Str;

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
    protected static $rules = [
        'reg',//注册用户
        'email',//邮箱
    ];

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
            return "普通消息";
        } else {
            if (!empty($res[1][0]) && $res[1][0]!="") {
                return "指令消息" . $res[1][0];
            } else {
                return "指令消息" . $res[2][0] . "-----" . $res[3][0];
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

    protected static function telegram($msg, $params)
    {

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
}
