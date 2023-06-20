<?php

namespace app\common\job;

use think\Db;
use think\Model;
use think\Exception;
use think\queue\Job;
use app\common\library\Log;
use app\common\logic\Common;

use app\common\model\User;
use app\common\model\Order;
use app\common\model\OrderReleaseRecord;
use app\common\model\ProjectRebatesRateRecord;
use app\common\model\DividendCommissionConfig;

class OrderRelease
{
    public function fire(Job $job, $data)
    {
        Db::startTrans();
        try {
            //(new Log())->info('开始发送消息：' . json_encode($data));
            //插入查询支付数据，同时判断是否有未过期的支付数据，有就设为已过期


            /*if ($data['rebate_method'] == 1) {
                $flag = $this->dailyOrderRelease($data);
            } else if ($data['rebate_method'] == 2) {
                $flag = $this->monthlyRelease($data);
            } else {
                $flag = $this->fixedDateRelease($data);
            }*/

            $flag = $this->orderRelease($data);


            if ($flag) {
                //2、发送完成后 删除job
                $job->delete();
            } else {
                //任务轮询4次后删除
                if ($job->attempts() > 3) {
                    // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                    //$job->release(10);
                    // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                    //$job->failed();
                    // 第3种处理方式：删除任务
                    $job->delete();
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            // 执行报错的直接记录到下注单remark
            (new Order())->save(['remark' => $e->getMessage() . '---执行次数：' . $job->attempts()], ['order_id' => $data['order_id']]);
            // 队列执行失败
            (new Log())->error('OrderReleaseJob 发送消息队列执行失败：' . json_encode($data));
        }
    }

    // 消息队列执行失败后会自动执行该方法
    public function failed($data)
    {
        (new Log())->error('OrderReleaseJob 消息队列达到最大重复执行次数后失败：' . json_encode($data));
    }

    public function orderRelease($orderData)
    {
        $time = $orderData['release_operate_time'];
        //获取用户数据
        $userData = (new User())->where([
            'id' => $orderData['user_id'],
            'status' => 'normal'
        ])->find();
        if (empty($userData)) {
            if (!(new Order())->save([
                'status' => 3,
                'cancel_time' => $time,
                'remark' => '该投资单用户数据不存在或用户已被拉黑',
            ], ['order_id' => $orderData['order_id']])) {
                throw new Exception('订单：' . $orderData['order_id'] . ' 用户数据不存在');
            }
        } else {
            //能否获得返利，不能的直接跳过
            if ($userData['can_get_rebates'] != 1) {
                return true;
            }
            if ($orderData['rebate_method'] == 1) {
                //每日释放
                return $this->dailyOrderRelease($orderData, $time, $userData);
            } else if ($orderData['rebate_method'] == 2) {
                return $this->monthlyRelease($orderData, $time, $userData);
            } else {
                return $this->fixedDateRelease($orderData, $time, $userData);
            }
        }
        return true;
    }

    //每日释放
    public function dailyOrderRelease($orderData, $time, $userData)
    {
        $orderModel = new Order();
        //获取该期释放利率
        $dailyRebatesRate = $orderData['daily_rebates_rate'];
        //判断能否修改利率
        //if ($orderData['can_change_rebates_rate'] == 1) {
        $dailyRebatesRate = $this->getActualRebatesRate($orderData['project_id'], $time, $dailyRebatesRate);
        //}
        //当前释放利息数量（判断是否为利滚利模式）
        $nowReleaseAmount = isset($orderData['profit_rollover_model']) && $orderData['profit_rollover_model'] == 1 ? $dailyRebatesRate / 100 * ($orderData['investment_amount'] + $orderData['output']) : $dailyRebatesRate / 100 * $orderData['investment_amount'];
        //新产出数量
        $orderPars['output'] = $orderData['output'] + $nowReleaseAmount;
        //新已释放期数
        $newAlreadyPeriods = $orderData['already_periods'] + 1;
        //已释放期数
        $orderPars['already_periods'] = $newAlreadyPeriods;
        //新已释放期数==总释放期数（最后一期）
        if ($newAlreadyPeriods == $orderData['period']) {
            //改为已到期
            $orderPars['is_expired'] = 1;
            //下次释放时间改为空
            $orderPars['next_release_time'] = null;
            //到期时间改为当前时间
            $orderPars['expired_time'] = $time;
            //状态改为已结束
            $orderPars['status'] = 2;
            $orderPars['pick_up_principal'] = 1;
            $orderPars['pick_up_output'] = 1;
            $orderPars['pick_up_time'] = $time;
            $orderPars['remark'] = '返利完成';
        } else {
            //下次释放时间
            $orderPars['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
        }

        //释放记录参数
        $releaseRecordPars = [
            'user_id' => $userData['id'],
            'order_id' => $orderData['order_id'],
            //释放的利息
            'release_number' => $nowReleaseAmount,
            'release_ratio' => $dailyRebatesRate,
            'release_periods' => $newAlreadyPeriods,
            'old_number' => $orderData['output'] ?: 0,
            'new_number' => $orderPars['output'],
            //返还的本金
            'return_principal' => 0,
            //是否返还利息
            'is_return_interest' => 0,
            //是否返还本金
            'is_return_principal' => 0,
            'type' => 1,
            'create_time' => $time
        ];

        //返还利息
        Common::changeUserTokenValue($userData, 'token_value', 1, 4, $nowReleaseAmount);
        //更新团队收益
        if (!$this->teamRelatedDataUpdate($userData, $nowReleaseAmount, $orderData['can_distribution_rebate'])) {
            throw new Exception('用户：' . $userData['id'] . '：修改团队收益数量错误');
        }
        $releaseRecordPars['is_return_interest'] = 1;
        //判断分红方式
        if (in_array($orderData['dividend_method'], [1, 3])) { //本息同返
            //返还的本金（判断是否为最后一期）
            $returnPrincipal = $newAlreadyPeriods == $orderData['period'] ? $orderData['actual_pay_investment_amount'] - $orderData['already_return_principal'] : $orderData['actual_pay_investment_amount'] / $orderData['period'];
            //返还当日本金
            Common::changeUserTokenValue((new User())->where(['id' => $orderData['user_id']])->find(), 'token_value', 1, 5, $returnPrincipal);
            //已返还本金
            $orderPars['already_return_principal'] = $orderData['already_return_principal'] + $returnPrincipal;
            $releaseRecordPars['return_principal'] = $returnPrincipal;
            $releaseRecordPars['is_return_principal'] = 1;
        } else { //先息后本
            //判断是否为最后一期
            if ($newAlreadyPeriods == $orderData['period']) {
                //返还所有本金
                Common::changeUserTokenValue((new User())->where(['id' => $orderData['user_id']])->find(), 'token_value', 1, 5, $orderData['actual_pay_investment_amount']);
                //已返还本金
                $orderPars['already_return_principal'] = $orderData['actual_pay_investment_amount'];
                $releaseRecordPars['return_principal'] = $orderData['actual_pay_investment_amount'];
                $releaseRecordPars['is_return_principal'] = 1;
            }
        }

        //创建释放记录
        if (!(new OrderReleaseRecord())->save($releaseRecordPars)) {
            throw new Exception("创建订单释放记录错误");
        }
        //释放订单
        if (!$orderModel->save($orderPars, ['order_id' => $orderData['order_id']])) {
            throw new Exception('订单：' . $orderData['order_id'] . ' 释放错误');
        }

        return true;
    }

    public function monthlyRelease($orderData, $time, $userData)
    {
        $orderModel = new Order();
        //先判断是否已经释放完
        if ($orderData['already_periods'] == $orderData['period']) {
            $newAlreadyPeriods = $orderData['period'];
            //判断是否已到期
            if (strtotime(date('Y-m-d', $time)) == strtotime(date('Y-m-d', $orderData['expired_time']))) {
                //改为已到期
                $orderPars['is_expired'] = 1;
                //下次释放时间改为空
                $orderPars['next_release_time'] = null;
                //到期时间改为当前时间
                $orderPars['expired_time'] = $time;
                //状态改为已结束
                $orderPars['status'] = 2;
                $orderPars['pick_up_principal'] = 1;
                $orderPars['pick_up_output'] = 1;
                $orderPars['pick_up_time'] = $time;
                $orderPars['remark'] = '返利完成';
            } else {
                $orderPars['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
            }
        } else {
            //获取该期释放利率
            $dailyRebatesRate = $orderData['daily_rebates_rate'];
            //判断能否修改利率
            //if ($orderData['can_change_rebates_rate'] == 1) {
            $dailyRebatesRate = $this->getActualRebatesRate($orderData['project_id'], $time, $dailyRebatesRate);
            //}
            //当前释放利息数量（判断是否为利滚利模式）
            $nowReleaseAmount = isset($orderData['profit_rollover_model']) && $orderData['profit_rollover_model'] == 1 ? $dailyRebatesRate / 100 * ($orderData['investment_amount'] + $orderData['output']) : $dailyRebatesRate / 100 * $orderData['investment_amount'];
            //新产出数量
            $orderPars['output'] = $orderData['output'] + $nowReleaseAmount;
            //新已释放期数
            $newAlreadyPeriods = $orderData['already_periods'] + 1;
            //已释放期数
            $orderPars['already_periods'] = $newAlreadyPeriods;
            //下次释放时间
            $orderPars['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
            //释放记录参数
            $releaseRecordPars = [
                'user_id' => $userData['id'],
                'order_id' => $orderData['order_id'],
                //释放的利息
                'release_number' => $nowReleaseAmount,
                'release_ratio' => $dailyRebatesRate,
                'release_periods' => $newAlreadyPeriods,
                'old_number' => $orderData['output'] ?: 0,
                'new_number' => $orderPars['output'],
                //返还的本金
                'return_principal' => 0,
                //是否返还利息
                'is_return_interest' => 0,
                //是否返还本金
                'is_return_principal' => 0,
                'type' => 1,
                'create_time' => $time
            ];
            //创建释放记录
            if (!(new OrderReleaseRecord())->save($releaseRecordPars)) {
                throw new Exception("创建订单释放记录错误");
            }
        }

        //判断当日是否为1号（1号才返利）
        if (intval(date('d', $time)) == $orderData['month_rebate_daily']) {
            $monthSTime = strtotime(date('Y-m-01', strtotime('-1 month', $time)));
            $monthETime = strtotime(date('Y-m-t', strtotime('-1 month', $time))) + 86399;
            $OrderReleaseRecordModel = new OrderReleaseRecord();
            //获取上月所有已释放的利息
            $releaseRecordList = $OrderReleaseRecordModel
                ->where([
                    'user_id' => $userData['id'],
                    'order_id' => $orderData['order_id'],
                    'is_return_interest' => 0,
                ])
                ->where('create_time', 'between', [
                    $monthSTime,
                    $monthETime,
                ])
                ->order('create_time', 'asc')
                ->select();
            //判断是否有利息
            if ($releaseRecordList) {
                //返还利息总额
                $returnInterestAmount = 0;
                foreach ($releaseRecordList as $rk => $rv) {
                    $returnInterestAmount += $rv['release_number'];
                    if (!$rv->save([
                        'is_return_interest' => 1
                    ])) {
                        throw new Exception('释放记录：' . $rv['record_id'] . ' 修改释放数据错误');
                    }
                }
                //返还利息
                Common::changeUserTokenValue($userData, 'token_value', 1, 4, $returnInterestAmount);
                //更新团队收益
                if (!$this->teamRelatedDataUpdate($userData, $returnInterestAmount, $orderData['can_distribution_rebate'])) {
                    throw new Exception('用户：' . $userData['id'] . '：修改团队收益数量错误');
                }

                //判断分红方式
                if (in_array($orderData['dividend_method'], [1, 3])) { //本息同返
                    $returnPrincipal = $newAlreadyPeriods == $orderData['period'] ? $orderData['actual_pay_investment_amount'] - $orderData['already_return_principal'] : $orderData['actual_pay_investment_amount'] / $orderData['period'] * count($releaseRecordList);
                    //返还当日本金
                    Common::changeUserTokenValue((new User())->where(['id' => $orderData['user_id']])->find(), 'token_value', 1, 5, $returnPrincipal);
                    //已返还本金
                    $orderPars['already_return_principal'] = $orderData['already_return_principal'] + $returnPrincipal;
                    if (!$OrderReleaseRecordModel->save([
                        'return_principal' => $orderData['actual_pay_investment_amount'] / $orderData['period'],
                        'is_return_principal' => 1,
                    ], [
                        'user_id' => $userData['id'],
                        'order_id' => $orderData['order_id'],
                        'is_return_interest' => 1,
                        //'create_time' => ['between', [$monthSTime, $monthETime]]
                    ])) {
                        throw new Exception('释放记录：修改释放本金数据错误');
                    }
                } else {
                    //判断是否为最后一期
                    if ($newAlreadyPeriods == $orderData['period']) {
                        //返还所有本金
                        Common::changeUserTokenValue((new User())->where(['id' => $orderData['user_id']])->find(), 'token_value', 1, 5, $orderData['actual_pay_investment_amount']);
                        //已返还本金
                        $orderPars['already_return_principal'] = $orderData['actual_pay_investment_amount'];
                        if (!$OrderReleaseRecordModel->save([
                            'return_principal' => $orderData['actual_pay_investment_amount'] / $orderData['period'],
                            'is_return_principal' => 1,
                        ], [
                            'user_id' => $userData['id'],
                            'order_id' => $orderData['order_id'],
                            'is_return_principal' => 0,
                        ])) {
                            throw new Exception('释放记录：修改释放本金数据错误');
                        }
                    }
                }
            }
        }

        //释放订单
        if (!$orderModel->save($orderPars, ['order_id' => $orderData['order_id']])) {
            throw new Exception('订单：' . $orderData['order_id'] . ' 释放错误');
        }

        return true;
    }

    public function fixedDateRelease($orderData, $time, $userData)
    {
        $orderModel = new Order();
        //是否到期
        $isExpired = strtotime(date('Y-m-d', $time)) == strtotime(date('Y-m-d', $orderData['expired_time'])) ? 1 : 0;
        //判断是否已释放完
        if ($orderData['already_periods'] == $orderData['period']) {
            $newAlreadyPeriods = $orderData['already_periods'];
            //判断是否已到期
            if ($isExpired) {
                //改为已到期
                $orderPars['is_expired'] = 1;
                //下次释放时间改为空
                $orderPars['next_release_time'] = null;
                //到期时间改为当前时间
                $orderPars['expired_time'] = $time;
                //状态改为已结束
                $orderPars['status'] = 2;
                $orderPars['pick_up_principal'] = 1;
                $orderPars['pick_up_output'] = 1;
                $orderPars['pick_up_time'] = $time;
                $orderPars['remark'] = '返利完成';
            } else {
                $orderPars['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
            }

        } else {
            //获取该期释放利率
            $dailyRebatesRate = $orderData['daily_rebates_rate'];
            //判断能否修改利率
            //if ($orderData['can_change_rebates_rate'] == 1) {
            $dailyRebatesRate = $this->getActualRebatesRate($orderData['project_id'], $time, $dailyRebatesRate);
            //}
            //当前释放利息数量（判断是否为利滚利模式）
            $nowReleaseAmount = isset($orderData['profit_rollover_model']) && $orderData['profit_rollover_model'] == 1 ? $dailyRebatesRate / 100 * ($orderData['investment_amount'] + $orderData['output']) : $dailyRebatesRate / 100 * $orderData['investment_amount'];
            //新产出数量
            $orderPars['output'] = $orderData['output'] + $nowReleaseAmount;
            //新已释放期数
            $newAlreadyPeriods = $orderData['already_periods'] + 1;
            //已释放期数
            $orderPars['already_periods'] = $newAlreadyPeriods;
            //下次释放时间
            $orderPars['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
            if ($isExpired) {
                //改为已到期
                $orderPars['is_expired'] = 1;
                //下次释放时间改为空
                $orderPars['next_release_time'] = null;
                //到期时间改为当前时间
                $orderPars['expired_time'] = $time;
                //状态改为已结束
                $orderPars['status'] = 2;
                $orderPars['pick_up_principal'] = 1;
                $orderPars['pick_up_output'] = 1;
                $orderPars['pick_up_time'] = $time;
                $orderPars['remark'] = '返利完成';
            }
            //释放记录参数
            $releaseRecordPars = [
                'user_id' => $userData['id'],
                'order_id' => $orderData['order_id'],
                //释放的利息
                'release_number' => $nowReleaseAmount,
                'release_ratio' => $dailyRebatesRate,
                'release_periods' => $newAlreadyPeriods,
                'old_number' => $orderData['output'] ?: 0,
                'new_number' => $orderPars['output'],
                //返还的本金
                'return_principal' => 0,
                //是否返还利息
                'is_return_interest' => 0,
                //是否返还本金
                'is_return_principal' => 0,
                'type' => 1,
                'create_time' => $time
            ];
            //创建释放记录
            if (!(new OrderReleaseRecord())->save($releaseRecordPars)) {
                throw new Exception("创建订单释放记录错误");
            }
        }

        //判断已释放的期数能否整除定期返间隔时间||已到期
        if ($newAlreadyPeriods % $orderData['recurring_rebate_interval_date'] == 0 || $isExpired == 1) {
            //本息定期同返
            $monthSTime = strtotime('-' . ($orderData['recurring_rebate_interval_date'] - 1) . ' day', strtotime(date('Y-m-d', $time)));
            $monthETime = strtotime(date('Y-m-d', $time)) + 86399;
            $OrderReleaseRecordModel = new OrderReleaseRecord();
            //获取上个间隔时间所有已释放的利息
            $releaseRecordList = $OrderReleaseRecordModel
                ->where([
                    'user_id' => $userData['id'],
                    'order_id' => $orderData['order_id'],
                    'is_return_interest' => 0,
                    'is_return_principal' => 0,
                ])
                ->where('create_time', 'between', [
                    $monthSTime,
                    $monthETime,
                ])
                ->order('create_time', 'asc')
                ->select();
            if ($releaseRecordList) {
                //返还利息总额
                $returnInterestAmount = 0;
                foreach ($releaseRecordList as $rk => $rv) {
                    $returnInterestAmount += $rv['release_number'];
                    if (!$rv->save([
                        'is_return_interest' => 1
                    ])) {
                        throw new Exception('释放记录：' . $rv['record_id'] . ' 修改释放数据错误');
                    }
                }
                //返还利息
                Common::changeUserTokenValue($userData, 'token_value', 1, 4, $returnInterestAmount);
                //更新团队收益
                if (!$this->teamRelatedDataUpdate($userData, $returnInterestAmount, $orderData['can_distribution_rebate'])) {
                    throw new Exception('用户：' . $userData['id'] . '：修改团队收益数量错误');
                }
                //返还本金
                $returnPrincipal = $isExpired == 1 ? $orderData['actual_pay_investment_amount'] - $orderData['already_return_principal'] : $orderData['actual_pay_investment_amount'] / $orderData['period'] * count($releaseRecordList);
                //返还当日本金
                Common::changeUserTokenValue((new User())->where(['id' => $orderData['user_id']])->find(), 'token_value', 1, 5, $returnPrincipal);
                //已返还本金
                $orderPars['already_return_principal'] = $orderData['already_return_principal'] + $returnPrincipal;
                if (!$OrderReleaseRecordModel->save([
                    'return_principal' => $orderData['actual_pay_investment_amount'] / $orderData['period'],
                    'is_return_principal' => 1,
                ], [
                    'user_id' => $userData['id'],
                    'order_id' => $orderData['order_id'],
                    'is_return_interest' => 1,
                    //'create_time' => ['between', [$monthSTime, $monthETime]]
                ])) {
                    throw new Exception('释放记录：修改释放本金数据错误');
                }
            }
        }

        //释放订单
        if (!$orderModel->save($orderPars, ['order_id' => $orderData['order_id']])) {
            throw new Exception('订单：' . $orderData['order_id'] . ' 释放错误');
        }

        return true;
    }

    //获取实际返利利率
    public function getActualRebatesRate($projectId, $time, $oldRate)
    {
        //获取该项目最新日化利率
        $projectRebatesRateRecordData = (new ProjectRebatesRateRecord())->where([
            'project_id' => $projectId,
            'start_time' => ['<', $time],
            'end_time' => ['>=', $time],
        ])->order('create_time', 'desc')->find();
        return $projectRebatesRateRecordData && $projectRebatesRateRecordData['daily_rebates_rate'] > 0 ? $projectRebatesRateRecordData['daily_rebates_rate'] : $oldRate;
    }

    /**
     * 更新团队累计收益相关数据
     * @param $userData
     * @param $nowReleaseAmount
     * @return bool
     * @throws Exception
     */
    private function teamRelatedDataUpdate($userData, $nowReleaseAmount, $canDistributionRebate = 0): bool
    {
        if ($referrerLinkArr = Common::getUserAscReferrerLink($userData)) {
            //获取下线提成配置
            $dividendCommissionConfigData = (new DividendCommissionConfig())->where(['status' => 1])->order('sub_level', 'asc')->select();
            if ($dividendCommissionConfigData) {
                $dividendCommissionConfigData = array_column(collection($dividendCommissionConfigData)->toArray(), 'commission_ratio', 'sub_level');
            }
            foreach ($referrerLinkArr as $k => $v) {
                $superiorEditPars = [];
                //团队上级数据
                $superiorUserData = (new User())->where('invite_code', $v)->find();
                if ($superiorUserData) {
                    //团队累计收益数量
                    $superiorEditPars['team_cumulative_earnings_value'] = $superiorUserData['team_cumulative_earnings_value'] + $nowReleaseAmount;
                }
                //三级分销返利（前三代直推且投资过才有）
                if ($canDistributionRebate == 1 && $superiorUserData['status'] == 'normal' && $superiorUserData['is_audit'] == 1 && $superiorUserData['is_valid'] == 1 && $superiorUserData['can_get_dividends'] == 1 && array_key_exists(($k + 1), $dividendCommissionConfigData) && $dividendCommissionConfigData[$k + 1] > 0) {
                    /*switch ($k) {
                        case 0:
                            //分销返利比例
                            $distributionRebateRatio = 16;
                            break;
                        case 1:
                            $distributionRebateRatio = 5;
                            break;
                        default:
                            $distributionRebateRatio = 1;
                            break;
                    }*/
                    Common::changeUserTokenValue($superiorUserData, 'token_value', 1, 9, ($dividendCommissionConfigData[$k + 1] / 100 * $nowReleaseAmount));
                }
                if ($superiorEditPars && !$superiorUserData->save($superiorEditPars)) {
                    return false;
                }
            }
        }
        return true;
    }
}