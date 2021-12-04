<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    14:28
 * Email:   yxw770@gmail.com
 */


namespace App\Services;


use App\Contracts\TgOPStepContract;
use App\Models\TgMsgStep;
use Illuminate\Database\Eloquent\Model;

class TgOPStepService extends BaseService implements TgOPStepContract
{
    /**
     * 新增步骤
     *
     * @param int $tg_userid telegram的用户id
     * @param int $get_msg_id 触发的消息id
     * @param int $type 步骤类型
     * @param int $bot_id 机器人id
     * @param int $expired_at 过期时间多久过期，秒
     * @param int $step 第几步
     *
     * @return bool
     */
    public function add(int $tg_userid, int $get_msg_id, int $type, int $bot_id, int $expired_at = 5 * 60, int $step = 0): bool
    {
        // TODO: Implement add() method.

        $tgMsgStep = new TgMsgStep;
        $tgMsgStep->tg_userid = $tg_userid;
        $tgMsgStep->get_msg_id = $get_msg_id;
        $tgMsgStep->type = $type;
        $tgMsgStep->step = $step;
        $tgMsgStep->create_at = time();
        $tgMsgStep->expired_at = $tgMsgStep->create_at + $expired_at;
        $tgMsgStep->bot_id = $bot_id;
        $tgMsgStep->is_del = 0;
        return $tgMsgStep->save();

    }

    /**
     * 是否存在
     *
     * @param int $tg_userid telegram的用户id
     * @param int $type 步骤类型
     * @param int $bot_id 机器人id
     * @param int $expired_time 过期时间多久过期，秒,为0则不变
     * @return mixed
     */
    public function isExist(int $tg_userid, int $type, int $bot_id, int $expired_time = 0): array
    {
        // TODO: Implement isExist() method.
        $condition = [
            ['tg_userid', '=', $tg_userid],
            ['bot_id', '=', $bot_id],
            ['is_del', '=', 0],
            ['expired_at', '>=', time()]
        ];
        if ($type != 0) {
            array_push($condition, ['type', '=', $type]);
        }
        $tgMsgStep = TgMsgStep::where($condition)->orderByDesc("id")->first();
        if (empty($tgMsgStep)) {
            return $this->ret(0);
        } else {
            if ($expired_time > 0) {
                $tgMsgStep->expired_at = time() + $expired_time;
                $tgMsgStep->save();
            }
            return $this->ret(1, 'ok', $tgMsgStep);

        }
    }

    /**
     * 更改步骤步数
     *
     * @param int $tg_userid telegram上的用户id
     * @param int $type 步骤类型
     * @param int $bot_id 机器人id
     * @param int|float $expired_at 过期时间多久过期，秒,为0则不变
     * @param int $step 步骤
     * @return bool
     */
    public function setStep(int $tg_userid, int $type, int $bot_id, int $expired_at = 5 * 60, int $step = 0): array
    {
        // TODO: Implement setStep() method.
        $condition = [
            ['tg_userid', '=', $tg_userid],
            ['bot_id', '=', $bot_id],
            ['is_del', '=', 0],
            ['expired_at', '>=', time()],
            ['type', '=', $type]
        ];
        $tgMsgStep = TgMsgStep::where($condition)->orderByDesc("id")->first();
        if (empty($tgMsgStep)) {
            return $this->ret(0);
        } else {
            if ($expired_at > 0) {
                $tgMsgStep->expired_at = time() + $expired_at;
            }
            $tgMsgStep->step = $step;
            $tgMsgStep->save();
            return $this->ret(1, 'ok', $tgMsgStep);

        }
    }





}
