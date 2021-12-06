<?php
/**
 * Created by PhpStorm.
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    09:40
 * Email:   yxw770@gmail.com
 */


namespace App\Utils\Telegram;

use App\Exceptions\TelegramSDKException;

/**
 * telegram 消息开发类
 *
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    09:41
 * Email:   yxw770@gmail.com
 * Class Message
 * @package App\Utils\Telegram
 */
class Message
{

    /** Telegram Bot API URL. */
    const BASE_BOT_URL = 'https://Api.telegram.org/bot';
    /**  机器人token. */
    protected $accessToken;

    public function __construct($token = null)
    {
        $this->accessToken = $token ;
        $this->validateAccessToken();
    }
    /**
     * @throws TelegramSDKException
     */
    private function validateAccessToken()
    {
        if (! $this->accessToken || ! is_string($this->accessToken)) {
            throw  new TelegramSDKException(['msg'=>'机器人accessToken异常','code'=>500]);
        }
    }
}
