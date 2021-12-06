<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-06
 * Time:    17:36
 * Email:   yxw770@gmail.com
 */


namespace App\Contracts;

/**
 * Server酱接口类
 * Interface ServerChanContract
 * @package App\Contracts
 */
interface ServerChanContract
{
    /**
     * 设置SendKey
     * @param int $userid
     * @param string $SendKey
     * @return bool
     */
    public function setSendKey(int $userid ,string $SendKey):bool;
}
