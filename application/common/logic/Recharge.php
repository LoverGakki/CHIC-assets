<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use think\Exception;
use app\common\library\Log;
use app\common\model\AppConfig;

/**
 * 充值相关
 * Class Banner
 * @package app\common\logic
 */
class Recharge extends Model
{
    public $model = null;

    public $return = [
        'code' => 0,
        'msg' => '',
        'data' => null
    ];

    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->model = \think\Loader::model('UserRechargeRecord');
    }

    /**
     * 用户充值申请
     * @param $userId
     * @param $params
     * @return array
     */
    public function userRechargeApply($userId, $params): array
    {
        $return = $this->return;
        //获取线上充值最大值
        $onlineRechargeMax = AppConfig::getConfigDataValue('user_online_recharge_max');
        $onlineRechargeMax = $onlineRechargeMax ?: 1000000;
        Db::startTrans();
        try {
            if ($params['recharge_amount'] > $onlineRechargeMax) {
                throw new Exception('充值金额过大，请申请线下充值', 1);
            }

            $params['user_id'] = $userId;
            $params['status'] = 1;
            if (!$this->model->save($params)) {
                throw new Exception('用户：' . $userId . '：充值余额失败');
            }
            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('userRechargeApply：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }

}