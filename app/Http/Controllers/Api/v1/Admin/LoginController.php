<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-16
 * Time:    14:43
 * Email:   yxw770@gmail.com
 */


namespace App\Http\Controllers\Api\V1\Admin;


class LoginController extends AdminBaseController
{
    public function login()
    {
//        session()
//                session_start();

//        session(["a"=>666]);
        dump(session("a"));

        return J(0, '登入成功', ['access_token' => 'c262e61cd13ad99fc650e6908c7e5e65b63d2f32185ecfed6b801ee3fbdd5c0a']);
    }
}
