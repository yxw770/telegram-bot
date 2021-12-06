<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotCommand;
use Illuminate\Http\Request;

/**
 * 指令控制
 * Author:  Godfrey
 * Date:    2021-12-06
 * Time:    09:39
 * Email:   yxw770@gmail.com
 * Class CommandController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class CommandController extends AdminBaseController
{
    /**
     * @Name 添加机器人指令
     * @Author Godfrey<yxw770@gmail.com>
     * @Description 添加机器人指令API
     * @Date 2021-12-06 10:14
     * @Method POST
     * @param string command 指令
     * @param string dec     描述
     * @Return JSON
     **/
    public function add(Request $request)
    {
        $data = $request->only(['command', 'dec']);

        $data['create_at'] = time();
        dd(BotCommand::query()->insert($data));
    }
}
