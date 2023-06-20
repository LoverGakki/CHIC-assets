define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_coupons/index',
                    del_url: 'user/user_coupons/del',
                    multi_url: 'user/user_coupons/multi',
                    table: 'user_coupons'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "user_coupons_id",
                sortName: "user_coupons_id",
                sortOrder: "desc",
                method: "GET",
                search: true,
                commonSearch: true,
                showToggle: true,
                showRefresh: true,
                pagination: true, // 启动分页
                pageSize: 10, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [10, 20, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'user_coupons_id', title: '用户优惠券id', sortable: true},
                        // {field: 'user_id', title: '用户id', operate: "="},
                        {field: 'user.mobile', title: '会员手机', operate: "LIKE"},
                        {
                            field: "type",
                            title: "优惠券类型",
                            searchList: {
                                1: "现金券",
                                2: "加息券",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.label,
                        },
                        {field: 'name', title: '代金券名称', operate: "LIKE"},
                        {field: 'coupons_number', title: '金额（元）', operate: "BETWEEN"},
                        {field: 'coupons_use_limit', title: '可使用最低投资金额', operate: 'BETWEEN'},
                        {
                            field: 'pickup_channels',
                            title: '领取渠道',
                            searchList: {
                                1: "推送",
                                2: "投资赠送",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.label,
                        },
                        {field: 'can_use_project_type_name', title: '可使用项目板块', operate: false},
                        {
                            field: 'create_time',
                            title: __('领取时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: 'expiration_time',
                            title: __('失效时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                0: "禁止使用",
                                1: "未使用",
                                2: "已使用",
                                3: "已过期",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                        },

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
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
