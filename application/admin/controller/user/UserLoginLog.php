<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

class UserLoginLog extends Backend
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
        $this->model = model('common/UserLoginLog');
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
        $userModel =  model('common/User');
        if ($upperUserData = $userModel->where('mobile', $this->auth->mobile)->find()) {
            $whereArr['user.referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        } elseif ($upperUserData = $userModel->where('mobile', $this->auth->username)->find()) {
            $whereArr['user.referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with('user')
            ->where($where)
            ->where($whereArr)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

}