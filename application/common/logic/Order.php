<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use think\Exception;
use fast\Random;
use app\common\library\Log;
use app\common\model\user;
use app\common\model\UserCoupons;
use app\common\model\InvestmentProject;
use app\common\model\OrderReleaseRecord;
use app\common\model\InvestmentDividendCommissionConfig;

/**
 * 订单相关
 * Class Order
 * @package app\common\logic
 */
class Order extends Model
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
        $this->model = \think\Loader::model('Order');
    }


    public function getOrderList($userId, $status, $page, $pageSize)
    {
        $limit = ($page - 1) * $pageSize;
        $total = $this->model
            ->with('orderReleaseRecord')
            ->where([
                'user_id' => $userId,
                'status' => $status
            ])
            ->count();
        $list = $this->model
            ->with('orderReleaseRecord')
            ->where([
                'user_id' => $userId,
                'status' => $status
            ])
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            $projectModel = new InvestmentProject();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['order_id'];
                $row[$k]['name'] = $v['project_name'];
                $row[$k]['investment_amount'] = $v['investment_amount'];
                $row[$k]['create_time'] = date('Y-m-d', $v['create_time']);
                $row[$k]['end_time'] = date('Y-m-d', $v['expired_time']);
                //分红方式
                $row[$k]['rebate_method'] = $projectModel->getRebateMethodType()[$v['rebate_method']] . ' ' . $projectModel->getdividendMethodType()[$v['dividend_method']];
                $row[$k]['yesterday_earnings'] = $v['status'] == 1 && !empty($v['order_release_record']) && count($v['order_release_record']) >= 2 ? $v['order_release_record'][count($v['order_release_record']) - 2]['release_number'] : 0;
                $row[$k]['today_earnings'] = $v['status'] == 1 && !empty($v['order_release_record']) ? $v['order_release_record'][count($v['order_release_record']) - 1]['release_number'] : 0;
                $row[$k]['cumulative_earnings'] = !empty($v['order_release_record']) ? number_get_value(array_sum(array_column($v['order_release_record'], 'release_number')), 1000) : 0;
                $earningsList = [];
                if (!empty($v['order_release_record'])) {
                    foreach ($v['order_release_record'] as $ok => $ov) {
                        $earningsList[$ok]['id'] = $ov['record_id'];
                        $earningsList[$ok]['earnings_amount'] = $ov['release_number'];
                        $earningsList[$ok]['earnings_time'] = date('Y-m-d H:i', $ov['create_time']);
                    }
                }
                $row[$k]['earnings_list'] = $earningsList;
                $row[$k]['status'] = $v['status'];
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 创建订单并直接支付
     * @param $userData
     * @param $projectId
     * @param $investmentAmount
     * @param null $userCouponsId
     * @return array
     */
    public function createOrder($userData, $projectId, $investmentAmount, $userCouponsId = null): array
    {
        $return = $this->return;
        $time = time();
        $investmentProjectModel = new InvestmentProject();
        $UserCouponsModel = new UserCoupons();
        //获取项目数据
        $projectData = $investmentProjectModel
            ->with('project_type')
            ->where([
                'project_id' => $projectId,
                'investment_project.status' => 1
            ])->find();
        Db::startTrans();
        try {
            //判断项目是否存在
            if (!$projectData) {
                throw new Exception('项目不存在，请选择其它项目投资', 1);
            }
            //判断是否已购买项目（达到投资限购次数）
            $purchasedTimes = $this->model->where([
                'project_id' => $projectData['project_id'],
                'user_id' => $userData['id'],
                //'status' => ['in', '1,2']
                'status' => ['in', '1']
            ])->count();
            if ($purchasedTimes >= $projectData['buy_times_limit']) {
                throw new Exception('该项目已投资，请勿重复投资', 1);
            }
            //判断是否达到最低投资额
            if ($investmentAmount < $projectData['buy_min_number']) {
                throw new Exception('未达到起投金额', 1);
            }
            //判断是否超过限购投资额
            if ($investmentAmount > $projectData['buy_max_number']) {
                throw new Exception('超过限投金额', 1);
            }
            //实际需要支付金额
            $needPayAmount = $investmentAmount;
            //用户优惠券数据
            $userCouponsData = [];
            //判断能否使用优惠券
            if ($userCouponsId) {
                if ($projectData['can_use_coupons'] != 1) {
                    throw new Exception('该项目不能使用代金券', 1);
                }
                //获取优惠券查看是否可使用到该项目（项目类型）
                $userCouponsData = $UserCouponsModel->where([
                    'user_coupons_id' => $userCouponsId,
                    'user_id' => $userData['id'],
                    'status' => 1,
                    //购买金额超过可使用最低投资金额
                    'coupons_use_limit' => ['<=', $investmentAmount],
                    'expiration_time' => ['>=', $time],
                    //项目类型
                    ['exp', db()->raw("FIND_IN_SET(" . $projectData['project_type_id'] . ",project_type_ids)")]
                ])->find();
                if (empty($userCouponsData)) {
                    throw new Exception('代金券不适用', 1);
                } else {
                    //判断代金券类型
                    $needPayAmount = $userCouponsData['type'] == 1 ? $needPayAmount - $userCouponsData['coupons_number'] : $needPayAmount - $userCouponsData['coupons_number'] / 100 * $needPayAmount;
                    if ($needPayAmount <= 0) {
                        throw new Exception('投资金额过小，请增加投资', 1);
                    }
                }
            }
            //判断余额是否足够（扣除代金券金额后）
            if ($userData['token_value'] < $needPayAmount) {
                throw new Exception('余额不足，请先充值', 1);
            }

            //创建订单
            $orderPars = [
                'user_id' => $userData['id'],
                'project_id' => $projectData['project_id'],
                'project_name' => $projectData['name'],
                'order_code' => date('YmdHi') . Random::numeric(6),
                'investment_amount' => $investmentAmount,
                'actual_pay_investment_amount' => $needPayAmount,
                'token_pay_time' => $time,
                'daily_rebates_rate' => $projectData['daily_rebates_rate'],
                'period' => $projectData['project_cycle'],
                'rebate_method' => $projectData['rebate_method'],
                'dividend_method' => $projectData['dividend_method'],
                'month_rebate_daily' => $projectData['month_rebate_daily'] ?: 1,
                'recurring_rebate_interval_date' => $projectData['recurring_rebate_interval_date'],
                'already_periods' => 0,
                'is_expired' => 0,
                'output' => 0,
                'already_return_principal' => 0,
                'can_change_rebates_rate' => $projectData['can_change_rebates_rate'],
                'next_release_time' => strtotime(date('Y-m-d', $time)) + 86400,
                'pay_type' => 1,
                'can_distribution_rebate' => $projectData['can_distribution_rebate'],
                //是否为利滚利模式
                'profit_rollover_model' => $projectData['profit_rollover_model'],
                'status' => 1,
                'pick_up_principal' => 0,
                'pick_up_output' => 0,
            ];
            if ($userCouponsId && $userCouponsData) {
                $orderPars['user_coupons_id'] = $userCouponsId;
                $orderPars['coupons_amount'] = $userCouponsData['type'] == 1 ? $userCouponsData['coupons_number'] : $userCouponsData['coupons_number'] / 100 * $investmentAmount;
            }

            if ($orderPars['rebate_method'] == 1) {
                $orderPars['expired_time'] = strtotime('+' . ($orderPars['period'] + 1) . ' day', strtotime(date("Y-m-d", $time))) - 1;
            } elseif ($orderPars['rebate_method'] == 2) {
                $orderPars['expired_time'] = strtotime(date('Y-m', strtotime('+1 month', strtotime('+' . ($orderPars['period'] + 1) . ' day', strtotime(date("Y-m-d", $time))) - 1)) . '-' . $orderPars['month_rebate_daily']) + 86399;
            } elseif ($orderPars['rebate_method'] == 3) {
                $orderPars['expired_time'] = $orderPars['period'] % $orderPars['recurring_rebate_interval_date'] == 0 ? strtotime('+' . ($orderPars['period'] + 1) . ' day', strtotime(date("Y-m-d", $time))) - 1 : strtotime('+' . (intval($orderPars['period'] / $orderPars['recurring_rebate_interval_date']) * $orderPars['recurring_rebate_interval_date'] + $orderPars['recurring_rebate_interval_date'] + 1) . ' day', strtotime(date("Y-m-d", $time))) - 1;
            }
            if (!$this->model->save($orderPars)) {
                throw new Exception('用户：' . $userData['id'] . '：创建投资订单错误');
            }
            //修改优惠券状态
            if ($userCouponsData && !$userCouponsData->save([
                    'status' => 2,
                    'order_id' => $this->model->getLastInsID(),
                ])) {
                throw new Exception('优惠券：' . $userCouponsData['user_coupons_id'] . '：修改数据错误');
            }
            //判断是否可赠送优惠券、是否超过可赠送次数
            if ($projectData['is_give_coupons'] == 1 && $purchasedTimes < $projectData['give_coupons_times']) {
                if (!$UserCouponsModel->save([
                    'user_id' => $userData['id'],
                    'name' => $projectData['give_coupons_amount'] . '元的现金券',
                    'period' => $projectData['give_coupons_valid_days'],
                    'type' => 1,
                    'coupons_number' => $projectData['give_coupons_amount'],
                    'coupons_use_limit' => $projectData['give_coupons_use_limit'],
                    'project_type_ids' => $projectData['give_coupons_can_use_project_type'],
                    'status' => 1,
                    'pickup_channels' => 2,
                    'project_id' => $projectData['project_id'],
                    'expiration_time' => strtotime('+' . $projectData['give_coupons_valid_days'] . ' day', time()),
                ])) {
                    throw new Exception('用户：' . $userData['id'] . '：赠送优惠券失败');
                }
            }
            //扣除用户余额
            Common::changeUserTokenValue($userData, 'token_value', 2, 3, $needPayAmount);
            //修改项目已募集资金金额
            if (!$projectData->save([
                'funds_raised' => $projectData['funds_raised'] + $investmentAmount
            ])) {
                throw new Exception('项目：' . $projectData['project_id'] . '：修改项目募集资金失败');
            }
            //修改用户数据（改为激活用户）
            $userEditPars = [
                //累计投资金额
                'invest_total_amount' => $userData['invest_total_amount'] + $investmentAmount,
                //实际投资金额
                'actual_invest_total_amount' => $userData['actual_invest_total_amount'] + $needPayAmount,
                //个人业绩
                'myself_performance' => $userData['myself_performance'] + $investmentAmount,
                //自身团队业绩
                'team_performance' => $userData['team_performance'] + $investmentAmount,
            ];
            if ($userCouponsData) {
                //代金券累计支付金额
                $userEditPars['coupons_pay_amount'] = $userData['coupons_pay_amount'] + $orderPars['coupons_amount'];
            }
            //是否修改激活用户
            $change_valid = 0;
            if (!$userData['is_activated'] || $userData['is_activated'] == 0) {
                $userEditPars['is_activated'] = 1;
                $userEditPars['activation_time'] = $time;
                //团队激活人数+1
                $userEditPars['team_activated_number'] = $userData['team_activated_number'] + 1;
                $change_valid = 1;
            }
            if (!$userData->save($userEditPars)) {
                throw new Exception('用户：' . $userData['id'] . '：修改用户数据错误');
            }
            //修改团队业绩、团队激活直推
            if (!$this->teamRelatedDataUpdate($userData, $investmentAmount, $change_valid, $projectData['can_investment_distribution_rebate'])) {
                throw new Exception('用户：' . $userData['id'] . '：修改团队激活直推错误');
            }
            //处理该用户等级，判断是否能升级
            Common::userRoleLevelUp((new User())->where([
                'id' => $userData['id']
            ])->find());

            $return['code'] = 1;
            $return['msg'] = '投资成功';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('createOrder：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }

    /**
     * 更新团队相关数据
     * @param $userData
     * @param $buyNumber
     * @param int $change_valid
     * @return bool
     * @throws Exception
     */
    private function teamRelatedDataUpdate($userData, $buyNumber, int $change_valid = 0, $canInvestmentDistributionRebate = 0): bool
    {
        if ($referrerLinkArr = Common::getUserAscReferrerLink($userData)) {
            //获取下线提成配置
            $dividendCommissionConfigData = (new InvestmentDividendCommissionConfig())->where(['status' => 1])->order('sub_level', 'asc')->select();
            if ($dividendCommissionConfigData) {
                $dividendCommissionConfigData = array_column(collection($dividendCommissionConfigData)->toArray(), 'commission_ratio', 'sub_level');
            }
            foreach ($referrerLinkArr as $k => $v) {
                $superiorEditPars = [];
                //团队上级数据
                $superiorUserData = (new User())->where('invite_code', $v)->find();
                if ($superiorUserData) {
                    //修改团队业绩
                    $superiorEditPars['team_performance'] = $superiorUserData['team_performance'] + $buyNumber;
                    //需要修改激活用户（修改团队激活人数）
                    if ($change_valid == 1) {
                        //判断是否为直推上级，修改激活直推人数
                        if ($superiorUserData['id'] == $userData['superior_user_id']) {
                            $superiorEditPars['activated_direct_drive_number'] = $superiorUserData['activated_direct_drive_number'] + 1;
                        }
                        $superiorEditPars['team_activated_number'] = $superiorUserData['team_activated_number'] + 1;
                    }

                    //三级分销返利（前三代直推且投资过才有）
                    if ($canInvestmentDistributionRebate == 1 && $superiorUserData['status'] == 'normal' && $superiorUserData['is_audit'] == 1 && $superiorUserData['is_valid'] == 1 && $superiorUserData['can_get_dividends'] == 1 && array_key_exists(($k + 1), $dividendCommissionConfigData) && $dividendCommissionConfigData[$k + 1] > 0) {
                        Common::changeUserTokenValue($superiorUserData, 'token_value', 1, 9, ($dividendCommissionConfigData[$k + 1] / 100 * $buyNumber));
                    }
                }
                if ($superiorEditPars && !$superiorUserData->save($superiorEditPars)) {
                    return false;
                }
                Common::userRoleLevelUp((new User())->where([
                    'id' => $superiorUserData['id']
                ])->find());
            }
        }
        return true;
    }

    /**
     * 获取利息相关统计
     * @param $userData
     * @return array
     */
    public function getOrderInterestStatistics($userData): array
    {
        $unCollectedInterestAmount = (new OrderReleaseRecord())->where([
            'user_id' => $userData['id'],
            'is_return_interest' => 0
        ])->sum('release_number');

        return [
            'un_collected_interest_amount' => $unCollectedInterestAmount,
            'cumulative_earnings_value' => $userData['cumulative_earnings_value']
        ];
    }

    /**
     * 获取订单释放记录
     * @param $userId
     * @param $page
     * @param $pageSize
     */
    public function getOrderReleaseRecord($userId, $page, $pageSize)
    {
        $limit = ($page - 1) * $pageSize;
        $recordModel = new OrderReleaseRecord();
        $total = $recordModel
            ->with('orderData')
            ->where([
                'order_release_record.user_id' => $userId,
                'type' => 1
            ])
            ->count();
        $list = $recordModel
            ->with('orderData')
            ->where([
                'order_release_record.user_id' => $userId,
                'type' => 1
            ])
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['record_id'];
                $row[$k]['name'] = $v['order_data']['project_name'];
                $row[$k]['release_number'] = $v['release_number'];
                $row[$k]['create_time'] = date('Y-m-d', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

}