<?php

namespace app\admin\controller\coupons;

use think\Db;
use think\Exception;
use app\common\controller\Backend;
use app\common\model\Coupons;
use app\common\model\UserCoupons;
use app\common\model\UserLevelConfig;

class IssueCoupons extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,mobile';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/User');
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $userLevelConfigData = (new UserLevelConfig())->where(['status' => 1])->order('level_number', 'asc')->select();
            $couponsData = (new Coupons())->where(['status' => 1])->order('create_time', 'desc')->select();
            $this->view->assign([
                'user_level' => collection($userLevelConfigData)->toArray(),
                'coupons' => collection($couponsData)->toArray(),
            ]);

            return $this->view->fetch();
        }
        $mobile = $this->request->request('mobile');
        $role_level = $this->request->request('role_level');
        if (empty($mobile) && $role_level == '') {
            return json(['total' => 0, 'rows' => []]);
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function group_issue_coupons()
    {
        if ($this->request->isAjax()) {
            $mobile = $this->request->post('mobile');
            $roleLevel = $this->request->post('role_level');
            $couponsId = $this->request->post('coupons');
            if (empty($mobile) && $roleLevel == '') {
                $this->error('请选择要发放的用户');
            }
            if (!$couponsId) {
                $this->error('请选择要发放的优惠券');
            }

            $couponsData = (new Coupons())->where([
                'coupons_id' => $couponsId,
                'status' => 1
            ])->find();
            if (empty($couponsData)) {
                $this->error('该优惠券数据不存在');
            }
            $userIds = [];
            if ($mobile) {
                $mobileUserData = $this->model->where(['mobile' => $mobile])->find();
                if (!$mobileUserData) {
                    $this->error('该手机号码用户不存在');
                }
                if ($mobileUserData['status'] != 'normal') {
                    $this->error('该手机号码用户已拉黑');
                }
                if ($mobileUserData['is_audit'] != 1) {
                    $this->error('该手机号码用户未审核');
                }
                $userIds[] = $mobileUserData['id'];
            }
            if ($roleLevel != '') {
                $userList = $this->model->where([
                    'status' => 'normal',
                    'is_audit' => 1,
                    'role_level' => $roleLevel,
                ])->select();
                if ($userList) {
                    $userIds = array_merge($userIds, array_column(collection($userList)->toArray(), 'id'));
                }
            }
            if (!$userIds) {
                $this->error('暂无符合发放条件用户');
            }
            $userIds = array_values(array_unique($userIds));

            Db::startTrans();
            try {
                $addPars = [];
                foreach ($userIds as $k => $v) {
                    $addPars[] = [
                        'user_id' => $v,
                        'coupons_id' => $couponsData['coupons_id'],
                        'name' => $couponsData['name'],
                        'period' => $couponsData['period'],
                        'type' => $couponsData['type'],
                        'coupons_number' => $couponsData['coupons_number'],
                        'coupons_use_limit' => $couponsData['coupons_use_limit'],
                        'project_type_ids' => $couponsData['project_type_ids'],
                        'status' => 1,
                        'pickup_channels' => 1,
                        'expiration_time' => strtotime('+' . $couponsData['period'] . ' day', time())
                    ];
                }

                if (!(new UserCoupons())->saveAll($addPars)) {
                    throw new Exception('优惠券发放错误');
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->success('发放成功');
        }
        $this->error('请求方式错误');
    }

}