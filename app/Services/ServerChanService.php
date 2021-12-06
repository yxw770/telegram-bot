<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-06
 * Time:    17:51
 * Email:   yxw770@gmail.com
 */


namespace App\Services;


use App\Contracts\ServerChanContract;
use App\Models\Serverchan;

/**
 * Server酱服务接口类
 * Author:  Godfrey
 * Date:    2021-12-06
 * Time:    17:51
 * Email:   yxw770@gmail.com
 * Class ServerChanService
 * @package App\Services
 */
class ServerChanService extends BaseService implements ServerChanContract
{
    /**
     * 设置SendKey
     * @param int $userid 用户id
     * @param string $SendKey Sendkey
     * @return bool
     */
    public function setSendKey(int $userid, string $SendKey): bool
    {

        // TODO: Implement setSendKey() method.
        return !empty(Serverchan::updateOrInsert(
            ['userid' => $userid],
            ['SendKey' => $SendKey]
        ));
    }
}
