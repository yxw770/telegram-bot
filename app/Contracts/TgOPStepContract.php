<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    14:22
 * Email:   yxw770@gmail.com
 */


namespace App\Contracts;

/**
 * 定义telegram 操作步骤接口类
 * Interface TgOPStepContract
 * @package App\Contracts
 */
interface TgOPStepContract
{
    /**
     * 新增步骤
     *
     * @param int $tg_userid        telegram的用户id
     * @param int $get_msg_id       触发的消息id
     * @param int $type             步骤类型
     * @param int $bot_id           机器人id
     * @param int $expired_at       过期时间多久过期，秒
     * @param int $step             第几步
     *
     * @return bool
     */
    public function add(int $tg_userid, int $get_msg_id, int $type, int $bot_id, int $expired_at = 5 * 60, int $step = 0): bool;

    /**
     * 是否存在
     *
     * @param int $tg_userid            telegram的用户id
     * @param int $type                 步骤类型
     * @param int $bot_id               机器人id
     * @param int $expired_time         过期时间多久过期，秒,为0则不变
     * @return mixed
     */
    public function isExist(int $tg_userid, int $type, int $bot_id, int $expired_time = 0): array;

    /**
     * 设置步数
     * @return mixed
     */
    public function setStep();
}
