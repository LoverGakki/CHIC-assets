define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project_config/user_level_config/index',
                    add_url: 'project_config/user_level_config/add',
                    edit_url: 'project_config/user_level_config/edit',
                    del_url: 'project_config/user_level_config/del',
                    multi_url: 'project_config/user_level_config/multi',
                    // dragsort_url: 'ajax/weigh',
                    table: 'user_level_config'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "level_config_id",
                sortName: "level_config_id",
                sortOrder: "desc",
                method: "GET",
                search: true,
                commonSearch: true,
                showToggle: true,
                showRefresh: true,
                pagination: true, // 启动分页
                pageSize: 20, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [20, 50, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'level_config_id', title: '配置id', sortable: true},
                        {field: 'level_number', title: '等级数字', operate: "BETWEEN"},
                        {field: 'dividend_bonus_interest_rate', title: '分红奖励利率', operate: "BETWEEN"},
                        {field: 'investments_total', title: '投资总和', operate: "BETWEEN"},
                        {field: 'balance_daily_return_rate', title: '余额宝日收益率', operate: "BETWEEN"},
                        {field: 'upgrade_credit', title: '升级赠送金额', operate: "BETWEEN"},
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                1: "启用",
                                0: "禁用",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
                            custom: {1: "success"},
                        },
                        {
                            field: 'create_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            //width: 150
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
