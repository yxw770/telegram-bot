<?php

namespace Telegram\Bot\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class TelegramSDKException.
 */
class TelegramSDKException extends Exception
{
    protected $message;
    protected $code;
    protected $previous;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {

        parent::__construct($message, $code, $previous);
        if (empty($message)) {
            $this->message = self::getMessage();
        } else {
            $this->message = $message;
        }
        if (empty($code)) {
            $this->code = self::getCode();
        } else {
            $this->code = $code;
        }
        if (empty($previous)) {
            $this->previous = self::getPrevious();
        } else {
            $this->previous = $previous;
        }
        Log::error($this->message, parent::getTrace());
    }

    public function render($request)
    {
        $return = [
            'code' => $this->code,
            'msg' => $this->message,
            'data' => $this->previous->getTrace(),
            'timestamp' => time(),
        ];
        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Thrown when token is not provided.
     *
     * @param $tokenEnvName
     *
     * @return TelegramSDKException
     */
    public static function tokenNotProvided($tokenEnvName): self
    {

        return new static('Required "token" not supplied in config and could not find fallback environment variable ' . $tokenEnvName . '');
    }
}
