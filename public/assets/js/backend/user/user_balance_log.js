define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_balance_log/index',
                    table: 'user_balance_log'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "id",
                sortName: "id",
                sortOrder: "desc",
                method: "GET",
                search: true,
                commonSearch: true,
                showToggle: true,
                showRefresh: true,
                pagination: true, // 启动分页
                pageSize: 10, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [10, 50, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: '记录', operate: "=", sortable: true},
                        {field: 'user_id', title: '用户id', operate: "="},
                        {field: 'user.username', title: '用户名', operate: "LIKE"},
                        {field: 'money', title: '变更余额', operate: "BETWEEN"},
                        {field: 'before', title: '变更前', operate: "BETWEEN"},
                        {field: 'after', title: '变更后', operate: "BETWEEN"},
                        {
                            field: "operate_type",
                            title: "操作类型",
                            searchList: {
                                1: "加",
                                2: "减",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                            custom: {"1": "success", "2": "danger"}
                        },
                        {
                            field: "change_type",
                            title: "变更类型",
                            //1 => '签到赠送余额',
                            //             2 => '用户充值',
                            //             3 => '投资扣除',
                            //             4 => '返还利息',
                            //             5 => '返还本金',
                            //             6 => '提现扣除',
                            //             7 => '余额宝日收益',
                            //             8 => '升级赠送',
                            //             9 => '分销返利',
                            //             10 => '平台扣除',
                            searchList: {
                                1: "签到赠送余额",
                                2: "用户充值",
                                3: "投资扣除",
                                4: "返还利息",
                                5: "返还本金",
                                6: "提现扣除",
                                7: "余额宝日收益",
                                8: "升级赠送",
                                9: "分销返利",
                                10: "平台扣除",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                        },
                        {field: 'memo', title: '备注', operate: "LIKE"},
                        {field: 'admin_id', title: '操作管理员id', operate: "="},
                        {
                            field: 'create_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
