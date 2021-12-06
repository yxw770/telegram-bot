<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipGroup;
use Illuminate\Http\Request;

/**
 * 会员系统管理接口控制器
 * Author:  Godfrey
 * Date:    2021-12-06
 * Time:    11:23
 * Email:   yxw770@gmail.com
 * Class VipController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class VipController extends Controller
{
    /**
     * @Name 更新会员组
     * @Author Godfrey<yxw770@gmail.com>
     * @Description
     * @Date 2021-12-06 11:24
     * @Method  PUT
     * @Param int id 会员组编号
     * @param string name 会员组名称
     * @param json command_list
     * @Return JSON
     **/
    public function updateGroup(Request $request)
    {
        $data = $request->only(['command_list', 'name', 'id']);

        if (!empty($data['command_list'])) {
            $data['command_list'] = json_decode($data['command_list'], true);
        }
        $id = $data['id'];
        unset($data['id']);

        return J(VipGroup::where('id', $id)->update($data));
    }
}
