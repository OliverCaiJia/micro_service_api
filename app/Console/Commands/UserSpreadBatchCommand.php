<?php

namespace App\Console\Commands;

use App\Constants\SpreadConstant;
use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpreadBatch;
use Illuminate\Support\Facades\Log;

class UserSpreadBatchCommand extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSpreadBatchCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '延迟推送';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //Log::info('开始程序!', ['code' => 3333]);
        try {
            UserSpreadBatch::select()->where(['status' => 0, 'from' => SpreadConstant::SPREAD_FORM])
                ->chunk(100, function ($messages) {
                //SLogger::getStream()->info('message',['message'=>$messages]);
                $nowTime = date('Y-m-d H:i:s', time());
                foreach ($messages as $message) {
                    //SLogger::getStream()->info('时间未到',['message'=>$messages]);
                    $res = '';
                    //根据发送时间进行筛选
                    if ($message['send_at'] < $nowTime) {
                        //根据typeId获取typeNid
                        $typeInfo = UserSpreadFactory::fetchSpreadTypeNid($message['type_id']);
                        if (!empty($typeInfo)) {
                            //延迟推送信息
                            $speadInfo = UserSpreadFactory::fetchUserSpreadByMobile($message['mobile']);
                            //SLogger::getStream()->info('开始推送',['data'=>$speadInfo]);
                            $speadInfo['spread_log_id'] = $message['spread_log_id'];
                            $speadInfo['batch_id'] = $message['id'];
                            $speadInfo['type_id'] = $message['type_id'];
                            $speadInfo['type_nid'] = $typeInfo['type_nid'];
                            $speadInfo['choice_status'] = $typeInfo['choice_status'];
                            $speadInfo['limit'] = $typeInfo['limit'];
                            $speadInfo['total'] = $typeInfo['total'];
                            event(new UserSpreadBatchEvent($speadInfo));
                        }
                    }
                }
            });
        } catch (\Exception $ex) {
            \Log::error($ex);
        }
    }


}
