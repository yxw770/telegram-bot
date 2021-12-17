<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-16
 * Time:    14:35
 * Email:   yxw770@gmail.com
 */


namespace App\Exceptions;

use Throwable;

/**
 * API接口自定义异常类
 *
 * Author:  Godfrey
 * Date:    2021-12-16
 * Time:    14:35
 * Email:   yxw770@gmail.com
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends \Exception
{
    /**
     * API接口默认异常基类
     *
     * ApiException constructor.
     * @param string $message   传入信息
     * @param int $code         状态码
     * @param Throwable|null $previous  异常信息
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {

        parent::__construct($message, $code, $previous);
    }
}
