<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-11-25
 * Time:    11:47
 * Email:   yxw770@gmail.com
 */


/**
 * 记录文件日志
 */
function record_file_log($filename, $content)
{
    $log_path = storage_path("logs/") . $filename . '.log';
    $log_path = dirname($log_path);//父目录
    if (!is_dir($log_path)) {
        makedir($log_path);
    }
    file_put_contents(storage_path("logs/") . $filename . '.log', date('【Y-m-d H:i:s】') . "\r\n{$content}\r\n\r\n", FILE_APPEND);
}

/**
 * [makedir  迭代创建级联目录]
 * @param  [type] $path [目录路径]
 * @return [type]       [Boolean]
 */
function makedir($path)
{

    $arr = array();
    while (!is_dir($path)) {
        array_push($arr, $path);//把路径中的各级父目录压入到数组中去，直接有父目录存在为止（即上面一行is_dir判断出来有目录，条件为假退出while循环）
        $path = dirname($path);//父目录
    }
    if (empty($arr)) {//arr为空证明上面的while循环没有执行，即目录已经存在
        // echo $path,'已经存在';
        return true;
    }
    while (count($arr)) {
        $parentdir = array_pop($arr);//弹出最后一个数组单元
        mkdir($parentdir, 0777);//从父目录往下创建
    }

}


/**
 * @param string $dateTime 时间，如：2020-04-22 10:10:10
 * @param string $fromZone 时间属于哪个时区
 * @param string $toZone 时间转换为哪个时区的时间
 * @param string $format 时间格式，如：Y-m-d H:i:s
 * 时区选择参考：https://www.php.net/manual/zh/timezones.php 常见的如：UTC,Asia/Shanghai
 * 时间格式参考：https://www.php.net/manual/zh/datetime.formats.php
 *
 * @return string
 */
function dateTimeChangeByZone($dateTime, $fromZone, $toZone, $format = 'Y-m-d H:i:s')
{
    $dateTimeZoneFrom = new DateTimeZone($fromZone);
    $dateTimeZoneTo = new DateTimeZone($toZone);
    $dateTimeObj = DateTime::createFromFormat($format, $dateTime, $dateTimeZoneFrom);
    $dateTimeObj->setTimezone($dateTimeZoneTo);
    return $dateTimeObj->format($format);
}


/**
 * 返回接口数据
 *
 * @param int $code 状态码
 * @param string $msg 返回信息
 * @param array $data 数据
 * @param string $url 回访地址
 *
 * @return string json数据
 */
function J($code, $msg = '', $data = [], $url = null,$status=200)
{
    $return = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'url' => $url,
        'timestamp' => time(),
    ];

    return response()
        ->json($return,$status)
        ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
}


//验证是否URL
function validateURL($URL) {
    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $URL)) {
        return false;
    }
    return true;
}

//验证邮箱格式
function is_email($email) {
    $regx = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    if (!preg_match($regx, $email)) {
        return false;
    }
    return true;
}

/**
 * 将秒数转换为时间（年、天、小时、分、秒）
 *
 * @param number $time 秒数
 *
 * @return string
 */
function sec2Time($time) {
    if (is_numeric($time)) {
        $value = array(
            "years"   => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        if ($time >= 31556926) {
            $value["years"] = floor($time / 31556926);
            $time           = ($time % 31556926);
        }
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time          = ($time % 86400);
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time           = ($time % 3600);
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time             = ($time % 60);
        }
        $value["seconds"] = floor($time);
        $t                = '';
        if ($value["years"] > 0) {
            $t .= $value["years"] . "年";
        }
        if ($value["days"] > 0) {
            $t .= $value["days"] . "天";
        }
        if ($value["hours"] > 0) {
            $t .= $value["hours"] . "小时";
        }
        if ($value["minutes"] > 0) {
            $t .= $value["minutes"] . "分";
        }
        if ($value["seconds"] > 0) {
            $t .= $value["seconds"] . "秒";
        }
        return $t;
    } else {
        return (bool)false;
    }
}


/**
 * 设备或配置系统参数
 *
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 *
 * @return string|bool
 */
function sysconf(string $name, $value = null)
{
    static $config = [];
    if (empty($config)) {
        $config = \Illuminate\Support\Facades\DB::table('system_config')->pluck('value', 'name');
    }

    return isset($config[$name]) ? $config[$name] : '';
}
