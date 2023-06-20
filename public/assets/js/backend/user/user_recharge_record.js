define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_recharge_record/index',
                    multi_url: 'user/user_recharge_record/multi',
                    table: 'user_recharge_record'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "recharge_id",
                sortName: "recharge_id",
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
                        {field: 'recharge_id', title: '记录', operate: "=", sortable: true},
                        {field: 'user_id', title: '用户id', operate: "="},
                        {field: 'user.username', title: '用户名', operate: "LIKE"},
                        {field: 'recharge_amount', title: '充值金额', operate: "BETWEEN"},
                        {field: 'real_name', title: '充值人名称', operate: "LIKE"},
                        {field: 'bank_name', title: '银行卡名称', operate: "LIKE"},
                        {field: 'card_number', title: '银行卡号', operate: "LIKE"},
                        {
                            field: "type",
                            title: "操作类型",
                            searchList: {
                                1: "线上",
                                2: "线下",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                            custom: {"1": "success", "2": "info"}
                        },
                        {field: 'recharge_voucher', title: '充值凭证', operate: "LIKE"},
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                1: "充值中",
                                2: "已完成",
                                3: "已取消",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                            custom: {"1": "info", "2": "success", "3": "danger"}
                        },
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
