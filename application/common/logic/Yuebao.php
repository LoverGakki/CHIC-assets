<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use think\Exception;
use app\common\library\Log;
use app\common\model\AppConfig;
use app\common\model\UserLevelConfig;

/**
 * 余额宝相关
 * Class Yuebao
 * @package app\common\logic
 */
class Yuebao extends Model
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
        $this->model = \think\Loader::model('UserYuebaoEarningsRecord');
    }

    /**
     * 获取余额宝统计
     * @param $userData
     * @return array
     */
    public function getYuebaoStatistics($userData): array
    {
        $sTime = strtotime(date('Y-m-d', time()));
        $eTime = $sTime + 86399;
        $today_earnings_value = $this->model->where([
            'user_id' => $userData['id'],
            'change_type' => 2,
            'create_time' => ['between', [$sTime, $eTime]]
        ])->value('money');
        //获取日收益率
        $balance_daily_return_rate = (new UserLevelConfig())->where([
            'level_number' => $userData['role_level'],
            'status' => 1,
        ])->value('balance_daily_return_rate');

        return [
            //总金额
            'yuebao_token_value' => $userData['yuebao_token_value'],
            //今日收益
            'today_earnings_value' => $today_earnings_value ?: 0,
            //累计收益
            'cumulative_yuebao_earnings_amount' => $userData['cumulative_yuebao_earnings_amount'],
            //万分收益
            'ten_thousand_points_gain' => ($balance_daily_return_rate ? $balance_daily_return_rate / 100 : 0) * 10000,
            //日收益率
            'daily_return_rate' => $balance_daily_return_rate ?: 0
        ];
    }

    /**
     * getIntoAmountMin
     * @return int[]
     */
    public function getIntoAmountMin(): array
    {
        $intoAmountMin = AppConfig::getConfigDataValue('yuebao_into_amount_mix');
        return [
            'into_min' => $intoAmountMin ?: 100
        ];
    }

    /**
     * 转入余额宝
     * @param $userData
     * @param $intoAmount
     * @return array
     */
    public function yuebaoInto($userData, $intoAmount): array
    {
        $return = $this->return;
        $intoAmountMin = $this->getIntoAmountMin()['into_min'];
        Db::startTrans();
        try {
            if ($intoAmount < $intoAmountMin) {
                throw new Exception('转入金额过低', 1);
            }
            Common::changeUserTokenValue($userData, 'token_value', 2, 12, $intoAmount);
            Common::changeUserYuebaoTokenValue($userData, 1, 1, $intoAmount);

            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('yuebaoInto：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }

    /**
     * 获取余额宝余额变动记录
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getYuebaoBalanceList($userId, $page, $pageSize): array
    {
        $total = $this->model
            ->where([
                'user_id' => $userId
            ])
            ->count();
        $list = $this->model
            ->where([
                'user_id' => $userId
            ])
            ->order('create_time', 'desc')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['record_id'];
                $row[$k]['money'] = $v['money'];
                $row[$k]['return_rate'] = $v['return_rate'];
                $row[$k]['operate_type'] = $v['operate_type'];
                $row[$k]['change_type'] = $v['change_type'];
                $row[$k]['memo'] = $v['memo'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 余额宝转出
     * @param $userData
     * @param $outAmount
     * @return array
     */
    public function yuebaoOut($userData, $outAmount): array
    {
        $return = $this->return;
        //判断当日是否已提现过
        $sTime = strtotime(date('Y-m-d', time()));
        $eTime = $sTime + 86399;
        $outCount = $this->model->where([
            'user_id' => $userData['id'],
            'change_type' => 3,
            'create_time' => ['between', [$sTime, $eTime]]
        ])->count();
        Db::startTrans();
        try {
            if ($outCount >= 1) {
                throw new Exception('今天已申请转出，请明天再申请', 1);
            }
            Common::changeUserTokenValue($userData, 'token_value', 1, 13, $outAmount);
            Common::changeUserYuebaoTokenValue($userData, 2, 3, $outAmount);

            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('yuebaoOut：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }
}