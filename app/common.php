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
function J($code, $msg = '', $data = [], $url = null)
{
    $return = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'url' => $url,
        'timestamp' => time(),
    ];
    return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
}
