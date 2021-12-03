<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    10:07
 * Email:   yxw770@gmail.com
 */


namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class TelegramSDKException extends Exception
{
    /**
     * 状态码
     * @var int|mixed
     */
    public $code = 200;

    /**
     * 错误具体信息
     * @var mixed|string
     */
    public $message = 'json';

    /**
     * 构造函数，接收关联数组
     * BaseException constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct();
        if (!is_array($params)) {
            return ;
        }
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->message = $params['msg'];
        }
    }

    public function report()
    {
        //
        $result = [
            'code'  =>  $this->code,
            'msg'   => $this->message,
        ];
        //记录日志
        Log::error($this->message);
        return response()->json($result)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
    public function render($request)
    {
        $result = [
            'code'  =>  $this->code,
            'msg'   => $this->message,
        ];
        //记录日志
        Log::error($this->message);

        return response()->json($result)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

}
