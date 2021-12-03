<?php

namespace App\Http\Controllers\api\v1;

use App\Exceptions\TelegramSDKException;
use App\Http\Controllers\Controller;
use App\Models\TgBot;
use App\Utils\HandleMsg;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TgGetMsg;
use Telegram\Bot\Api;

class IndexController extends Controller
{
    /**
     * 机器人异步回调信息处理
     *
     * @param string $type 机器人类型
     * @param string $token 机器人的token
     * @param Request $request
     * @return false|string
     * @throws TelegramSDKException
     */
    public function notify($type, $token, Request $request)
    {
//        dd(sysconf("email_error_limit"));
        record_file_log('notify/' . date("Y-m-d") . '/' . date("H") . '-log', "【" . date('Y-m-d H:i:s') . "】\r\n" . file_get_contents("php://input") . "\r\n\r\n");
        $params = $request->input();

//        throw new TelegramSDKException::render("123");
        switch ($type) {
            case "telegram":
                return $this->telegram($token, $params);

            default:
                break;
        }

//        Log::info("123");
    }

    /**
     * telegram机器人信息
     *
     * @param string $token 机器人的token
     * @param array $params 返回参数信息
     * @return false|string
     */
    protected function telegram($token, $params)
    {


        $data = [];

        $condition = [
            ['token', '=', $token],
            ['state', '=', 1],
            ['is_del', '=', 0]
        ];

        $bot = TgBot::where($condition)->first();

        if (empty($bot)) {
            return false;
        }
//dd(is_email($params['message']['text']));
//        return false;
        $bot_id = $bot['id'];
        $params['bot_id'] = $bot_id;;
        record_file_log('telegram/' . date("Y-m-d") . "/bot-$bot_id/notify-" . date("H") . '-log', json_encode($params));
        $data = [
            'userid' => 0,
            'update_id' => $params['update_id'],
            'message_id' => $params['message']['message_id'],
            'tg_userid' => $params['message']['from']['id'],
            'create_at' => time(),
            'send_at' => $params['message']['date'],
            'msg' => base64_encode($params['message']['text']),
            'bot_id' => $bot_id
        ];

        try {
            DB::beginTransaction();
            $tgGetMsg = TgGetMsg::create($data);
            $tgGetMsg->save();

            $params1 = [
                'type' => "telegram",
                'tg_userid' => $data['tg_userid'],
                'send_at' => $data['send_at'],
                'message_id' => $data['message_id'],

                'token' => $bot['token'],
                'bot_id' => $bot_id,
                'msg_id'=>$tgGetMsg->id,
                'username'=>$params['message']['from']['username'],
                'first_name'=>$params['message']['from']['first_name'],
                'last_name'=>$params['message']['from']['last_name'],
            ];
            $res = HandleMsg::handleMsg(base64_decode($data['msg']), $params1);
            if (!$res){
                DB::rollBack();
                return $res;
            }
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'msg' => $e->getMessage(),
                'data' => $e->getTrace()
            ]);
        }
    }
}

