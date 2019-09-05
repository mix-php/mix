<?php

namespace App\WebSocket\Controllers;

use Mix\Concurrent\Coroutine\Channel;
use Mix\Redis\Coroutine\Connection;
use Mix\Redis\Pool\ConnectionPool;
use Swoole\WebSocket\Frame;
use App\WebSocket\Exceptions\ExecutionException;
use App\WebSocket\Helpers\JsonRpcHelper;
use App\WebSocket\Libraries\CloseConnection;
use App\WebSocket\Forms\JoinForm;
use App\WebSocket\Libraries\SessionStorage;

/**
 * Class JoinController
 * @package App\WebSocket\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class JoinController
{

    /**
     * @var string
     */
    protected $quitChannel;

    /**
     * JoinController constructor.
     */
    public function __construct()
    {
        $this->quitChannel = 'quit_' . md5(__FILE__);
    }

    /**
     * 加入房间
     * @param Channel $sendChan
     * @param SessionStorage $sessionStorage
     * @param $params
     * @return array
     */
    public function room(Channel $sendChan, SessionStorage $sessionStorage, $params)
    {
        // 验证数据
        $attributes = [
            'roomId' => (string)array_shift($params),
            'name'   => (string)array_shift($params),
        ];
        $model      = new JoinForm($attributes);
        $model->setScenario('room');
        if (!$model->validate()) {
            throw new ExecutionException($model->getError(), 100001);
        }

        // 订阅房间的频道
        if (!$sessionStorage->subChan || !$sessionStorage->subStopChan) {
            $subChan                     = new Channel();
            $subStopChan                 = new Channel();
            $sessionStorage->subChan     = $subChan;
            $sessionStorage->subStopChan = $subStopChan;
            $subChan->push([$model->roomId, $model->name]); // 推送订阅信息
            xgo(function () use ($subStopChan) {
                while (true) {
                    $stop = $subStopChan->pop();

                    // 由于phpredis无新增订阅功能，又无法在其他协程close实例，因此使用publish消息的方法平滑关闭redis
                    // 为避免与其他程序干扰，退出通道与类文件关联唯一
                    /** @var ConnectionPool $pool */
                    $pool  = context()->get('redisPool');
                    $redis = $pool->getConnection();
                    $redis->publish($this->quitChannel, true);
                    $redis->release();

                    if (!$stop) {
                        return;
                    }
                }
            });
            xgo(function () use ($sendChan, $subChan, $sessionStorage) {
                while (true) {
                    $data = $subChan->pop();
                    if (!$data) {
                        return;
                    }
                    list($roomId, $name) = $data;
                    try {
                        // 创建连接
                        /** @var $redis Connection $pool */
                        $redis = context()->get(Connection::class);
                        // 给其他订阅当前房间的连接发送加入消息
                        $message = JsonRpcHelper::notification('message.update', [
                            "{$name} joined the room",
                            $roomId,
                        ]);
                        $redis->publish("room_{$roomId}", $message);
                        // 保存当前房间
                        $sessionStorage->joinRoomId = $roomId;
                        // 订阅房间的频道
                        $channel = "room_{$roomId}";
                        $redis->subscribe([$this->quitChannel, $channel], function ($instance, $channel, $message) use ($sendChan) {
                            // 退出订阅
                            if ($channel == $this->quitChannel) {
                                /** @var $instance Connection */
                                $instance->close();
                                return;
                            }
                            // 发消息
                            $frame       = new Frame();
                            $frame->data = $message;
                            $sendChan->push($frame);
                        });
                    } catch (\Throwable $e) {
                        // redis连接异常断开处理
                        $sendChan->push(new CloseConnection());
                    }
                }
            });
        } else {
            $sessionStorage->subChan->push([$model->roomId, $model->name]); // 推送订阅信息
            $sessionStorage->subStopChan->push(true);
        }

        // 给当前连接发送加入消息
        return [
            'status' => 'success',
        ];
    }

}
