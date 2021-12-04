<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-04
 * Time:    16:24
 * Email:   yxw770@gmail.com
 */


namespace App\Services;


use App\Contracts\UserOPContract;
use Illuminate\Support\Facades\DB;

class UserOPService extends BaseService implements UserOPContract
{
    /**
     * 获取用户信息 从telegram上
     *
     * @param int $tg_userid telegram上的userid
     * @param int $bot_id 机器人id
     * @param int $state 用户状态默认-1即不加入条件
     * @param int $is_del 是否删除 默认0 -1代表不加入条件
     * @return array
     */
    public function getUserByTg(int $tg_userid, int $bot_id, int $state = -1, int $is_del = 0): array
    {
        // TODO: Implement getUserByTg() method.
        $where = [
            ['tg_user.tg_userid', '=', $tg_userid],
            ['tg_user.bot_id', '=', $bot_id],

        ];
        if ($state != -1) {
            array_push($where, ['user.state', '=', $state]);
        }
        if ($is_del != -1) {
            array_push($where, ['user.is_del', '=', $is_del]);
        }


        $tgUser = DB::table('user')
            ->join('tg_user', 'user.id', '=', 'tg_user.userid')
            ->where($where)
            ->select("user.email", "user.id","user.state")
            ->orderByDesc("id")
            ->first();

        if (empty($tgUser)) {
            return $this->ret(0);
        } else {
            return $this->ret(1, 'success', $tgUser);
        }
    }
}
