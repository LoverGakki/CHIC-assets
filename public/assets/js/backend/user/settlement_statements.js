define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/settlement_statements/index',
                    table: 'user'
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
                        {field: 'id', title: '用户ID', operate: "=", sortable: true},
                        {field: 'invite_code', title: '推荐码', operate: "="},
                        {field: 'mobile', title: '账号', operate: "LIKE"},
                        {
                            field: 'role_level',
                            title: __('用户等级'),
                            operate: '=',
                            formatter: Table.api.formatter.label,
                            searchList: {
                                0: __('V0'),
                                1: __('V1'),
                                2: __('V2'),
                                3: __('V3'),
                                4: __('V4'),
                                5: __('V5'),
                                6: __('V6'),
                            }
                        },
                        {field: 'real_name', title: '姓名', operate: false},
                        {
                            field: 'logintime',
                            title: __('登录时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                        },
                        {field: 'invest_total_amount', title: '累计投资金额', operate: "BETWEEN"},
                        {field: 'cumulative_recharge_amount', title: '累计充值金额', operate: "BETWEEN"},
                        {field: 'cumulative_withdrawals_amount', title: '累计提现金额', operate: "BETWEEN"},
                        {field: 'token_value', title: '余额', operate: "BETWEEN"},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')}
                        },
                        {
                            field: 'token_value', title: '推荐人', operate: "LIKE", formatter: function (value, row) {
                                if (row.superior_user) {
                                    return row.superior_user.invite_code + ' | ' + row.superior_user.mobile;
                                } else {
                                    return '';
                                }

                            }
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
