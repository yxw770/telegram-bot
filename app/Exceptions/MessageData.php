<?php

namespace App\Exceptions;

/**
 * 提示信息数据
 * Author:  Godfrey
 * Date:    2021-12-16
 * Time:    14:37
 * Email:   yxw770@gmail.com
 * Class MessageData
 * @package Modules\Common\Exceptions
 */
class MessageData
{
    const BAD_REQUEST = 'Bad request!';//服务器请求失败
    const INTERNAL_SERVER_ERROR = 'Server error!';//服务器错误
    const Ok = 'Success';//成功
    const PARES_ERROR = 'Server Pares Error';//服务器语法错误
    const Error = 'Error';//服务器语法错误，请注意查看信息
    const REFLECTION_EXCEPTION = 'Reflection exception';//服务器异常映射
    const RUNTIME_EXCEPTION = 'Runtime exception';//服务器运行期异常 运行时异常 运行异常 未检查异常
    const ERROR_EXCEPTION = 'error exception';//服务器框架运行出错
    const INVALID_ARGUMENT_EXCEPTION = 'Invalid argument exception';//数据库链接问题
    const MODEL_NOT_FOUND_EXCEPTION = 'Model not found exception！';//数据模型错误
    const QUERY_EXCEPTION = 'Db query exception';//数据库DB错误

    const COMMON_EXCEPTION = 'Common exception';//网络错误
    const API_Fail = 'Operation failed';//操作失败
    const API_ERROR_EXCEPTION = 'error';//操作失败
    const ADD_API_ERROR = 'Creation failed';//添加失败
    const ADD_API_SUCCESS = 'Created successfully';//添加成功
    const UPDATE_API_ERROR = 'Update failed';//修改失败
    const UPDATE_API_SUCCESS = 'Update Successfully';//修改成功
    const STATUS_API_ERROR = 'Failed to modify status';//修改状态失败
    const STATUS_API_SUCCESS = 'Modified status successfully';//改状态成功

    const DELETE_API_ERROR = 'Delete failed！';//删除失败
    const DELETE_API_SUCCESS = 'Delete successfully';//删除成功

    const DELETE_RECYCLE_API_ERROR = 'Recycle failed';//恢复失败
    const DELETE_RECYCLE_API_SUCCESS = 'Recycle successfully';//恢复成功

    const TOKEN_ERROR_KEY = 'ApiKey error';     // 70001apikey错误
    const TOKEN_ERROR_SET = 'Please Log in first!';        // 70002请先登录
    const TOKEN_ERROR_BLACK = 'Your token has been banned';  // 70003token 被拉黑
    const TOKEN_ERROR_EXPIRED = 'Your token has expired';  // 70004token 过期
    const TOKEN_ERROR_JWT = 'Please Log in first!';         //  70005请先登录
    const TOKEN_ERROR_JTB = 'Please Log in first!';          // 70006请先登录
}
