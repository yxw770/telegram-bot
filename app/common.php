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
function record_file_log($filename, $content) {
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
