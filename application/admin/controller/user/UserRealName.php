<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

class UserRealName extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,user_id';

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
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }

        $whereArr = [];
        if ($upperUserData = $this->model->where('mobile', $this->auth->mobile)->find()) {
            $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        } elseif ($upperUserData = $this->model->where('mobile', $this->auth->username)->find()) {
            $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->where($whereArr)
            ->where('id_card_number', 'not null')
            ->where('status', 'normal')
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    //实名认证审核
    public function real_name_audit()
    {
        if ($this->request->isPost()) {
            $user_id = $this->request->post('user_id');
            $is_audit = $this->request->post('is_audit');
            $remark = $this->request->post('remark');
            if (!in_array($is_audit, [1, 2])) {
                $this->error('参数错误');
            }
            $userData = $this->model->where([
                'id' => $user_id,
            ])->find();
            if (!$userData) {
                $this->error('用户数据不存在');
            }
            if ($userData['status'] != 'normal') {
                $this->error('该用户已拉黑');
            }
            if ($userData['is_audit'] == 1) {
                $this->error('用户已审核通过');
            }
            if (!$userData->save([
                'is_audit' => $is_audit,
                'audit_remark' => $remark,
            ])) {
                $this->error('审核错误');
            }
            $this->success('审核成功');
        }
        $this->error('请求方式错误');
    }

    //批量实名审核
    public function real_name_bulk_audit()
    {
        if ($this->request->isPost()) {
            $user_ids = $this->request->post('user_ids');
            $is_audit = $this->request->post('is_audit');
            $remark = $this->request->post('remark');
            if (!$user_ids) {
                $this->error('请选择需要审核的用户');
            }
            $user_ids = substr($user_ids, 0, -1);
            $userData = $this->model->where([
                'id' => ['in', $user_ids],
                'status' => 'normal',
                'is_audit' => ['in', '0,2'],
            ])->order('id', 'asc')->select();
            if (empty($userData)) {
                $this->success('审核成功');
            }
            foreach ($userData as $v) {
                if (!$v->save([
                    'is_audit' => $is_audit,
                    'audit_remark' => $remark,
                ])) {
                    $this->error('审核错误');
                }
            }
            $this->success('审核成功');
        }
        $this->error('请求方式错误');
    }

}