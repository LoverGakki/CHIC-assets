<?php

namespace app\common\logic;

use think\Model;
use app\common\model\ProjectType;
use app\common\job\OrderRelease;

/**
 * 项目相关
 * Class Project
 * @package app\common\logic
 */
class Project extends Model
{
    public $model = null;

    public $return = [
        'code' => 0,
        'msg' => '',
        'data' => null
    ];

    private $rebate_method_type = [];
    private $dividend_method_type = [];

    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->model = \think\Loader::model('InvestmentProject');
        $this->rebate_method_type = $this->model->getRebateMethodType();
        $this->dividend_method_type = $this->model->getdividendMethodType();
    }

    /**
     * 获取项目类型
     * @return array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProjectTypeList(): array
    {
        $list = (new ProjectType())
            ->where([
                'status' => 1
            ])
            ->order('weigh desc,create_time desc')
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['id'];
                $row[$k]['name'] = $v['type_name'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'rows' => $row
        ];
    }

    /**
     * 获取项目列表
     * @param $page
     * @param $pageSize
     * @param $projectTypeId
     * @return array
     */
    public function getProjectList($page, $pageSize, $projectTypeId = null): array
    {
        $limit = ($page - 1) * $pageSize;
        $where = [
            'status' => 1,
        ];
        if ($projectTypeId) {
            $where['project_type_id'] = $projectTypeId;
        }
        $total = $this->model
            ->with('investmentProjectLabel')
            ->where($where)
            ->count();
        $list = $this->model
            ->with('investmentProjectLabel')
            ->where($where)
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['project_id'];
                $row[$k]['project_type_id'] = $v['project_type_id'];
                $row[$k]['name'] = $v['name'];
                $row[$k]['head_img'] = $v['head_img'];
                $row[$k]['buy_times_limit'] = $v['buy_times_limit'];
                $label = [];
                if ($v['investment_project_label']) {
                    foreach ($v['investment_project_label'] as $lk => $lv) {
                        $label[$lk]['label_id'] = $lv['label_id'];
                        $label[$lk]['label_name'] = $lv['label_name'];
                    }
                }
                $row[$k]['label'] = $label;
                //获取当前项目是否有额外设定利率
                $row[$k]['daily_rebates_rate'] = (new OrderRelease())->getActualRebatesRate($v['project_id'], time(), $v['daily_rebates_rate']);
                $row[$k]['investment_authority'] = 1;
                $row[$k]['buy_min_number'] = ceil($v['buy_min_number']);
                $row[$k]['project_size'] = ceil($v['project_size'] / 10000) . '万';
                $row[$k]['completion_degree'] = ($v['funds_raised'] / $v['project_size']) * 100;
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 获取项目详情
     * @param $projectId
     * @return array
     */
    public function getProjectDetail($projectId): array
    {
        $data = $this->model
            ->where([
                'status' => 1,
                'project_id' => $projectId,
            ])
            ->find();
        $row = [];
        if ($data) {
            $row['id'] = $data['project_id'];
            $row['project_type_id'] = $data['project_type_id'];
            $row['name'] = $data['name'];
            $row['head_img'] = $data['head_img'];
            $row['rebate_method'] = $this->rebate_method_type[$data['rebate_method']] . ' ' . $this->dividend_method_type[$data['dividend_method']];
            $row['investment_risk'] = $data['investment_risk'];
            $row['guarantor_institutions'] = $data['guarantor_institutions'];
            $row['buy_min_number'] = $data['buy_min_number'];
            $row['project_size'] = ceil($data['project_size'] / 10000) . '万';;
            $row['project_cycle'] = $data['project_cycle'];
            $row['share_bonus'] = intval($data['buy_min_number'] * $data['daily_rebates_rate'] / 100);
            $row['completion_degree'] = ($data['funds_raised'] / $data['project_size']) * 100;
            $row['detail'] = $data['project_detail'];
            //收益释放表
            $earningsReleaseTable = [];
            //分红间隔时间
            $timesDay = 10;
            //分红间隔次数
            $tableCount = intval($data['project_cycle'] / $timesDay);
            //累计收益
            $earningsRelease = 0;
            $newTimesDay = 0;
            for ($i = 0; $i < $tableCount; $i++) {
                $earningsRelease += intval($timesDay * ($data['daily_rebates_rate'] / 100) * $data['buy_min_number']);
                $newTimesDay += $timesDay;
                $earningsReleaseTable[] = [
                    'id' => $i + 1,
                    'title' => $newTimesDay . '天累计分红',
                    'earnings_amount' => $earningsRelease,
                ];
            }
            $row['earnings_release_table'] = $earningsReleaseTable;
            $row['project_information'] = $data['project_information'];
            $row['can_use_coupons'] = $data['can_use_coupons'];
            $row['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
        }
        return $row;
    }

    /**
     * 获取项目优惠券列表
     * @return array[]
     */
    public function getProjectCoupons(): array
    {
        $list = $this->model
            ->where([
                'status' => 1,
                'is_give_coupons' => 1,
                'give_coupons_amount' => ['>', 0],
            ])
            ->order('give_coupons_amount', 'asc')
            ->select();
        $row = [];
        if ($list && count($list) >= 3) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['project_id'] = $v['project_id'];
                $row[$k]['project_type_id'] = '';
                $row[$k]['buy_min_number'] = $v['buy_min_number'];
                $row[$k]['give_coupons_amount'] = $v['give_coupons_amount'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        } else {
            $projectTypeId = (new ProjectType())->where('is_show_coupons', 1)->value('id');
            $projectTypeId = $projectTypeId ?: 1;
            $row = [
                [
                    'project_id' => '',
                    'project_type_id' => $projectTypeId,
                    'buy_min_number' => 39,
                    'give_coupons_amount' => 299,
                    'create_time' => date('Y-m-d H:i:s', time()),
                ],
                [
                    'project_id' => '',
                    'project_type_id' => $projectTypeId,
                    'buy_min_number' => 69,
                    'give_coupons_amount' => 699,
                    'create_time' => date('Y-m-d H:i:s', time()),
                ],
                [
                    'project_id' => '',
                    'project_type_id' => $projectTypeId,
                    'buy_min_number' => 99,
                    'give_coupons_amount' => 999,
                    'create_time' => date('Y-m-d H:i:s', time()),
                ],
            ];
        }
        return [
            'rows' => $row
        ];
    }

}