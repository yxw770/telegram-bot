<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-04
 * Time:    16:34
 * Email:   yxw770@gmail.com
 */


namespace App\Services;


class BaseService
{
    /**
     * 返回数据
     *
     * @param int    $status
     * @param string $msg
     * @param array  $data
     * @return array
     */
    protected function ret($status, $msg = '', $data = [])
    {
        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        ];
    }
}
