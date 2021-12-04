<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-04
 * Time:    16:23
 * Email:   yxw770@gmail.com
 */


namespace App\Contracts;

/**
 * 定义用户操作 接口类
 * Interface UserOPContract
 * @package App\Contracts
 */
interface UserOPContract
{
    /**
     * 获取用户信息 从telegram上
     *
     * @param int $tg_userid
     * @param int $bot_id
     * @param int $state
     * @param int $is_del
     * @return array
     */
    public function getUserByTg(int $tg_userid, int $bot_id, int $state = 0, int $is_del = 0): array;
}
