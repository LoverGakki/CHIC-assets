define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_bank_card/index',
                    del_url: 'user/user_bank_card/del',
                    multi_url: 'user/user_bank_card/multi',
                    table: 'user_bank_card'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "user_bank_card_id",
                sortName: "user_bank_card_id",
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
                        {field: 'user_bank_card_id', title: '银行卡id', sortable: true},
                        {field: 'user_id', title: '用户id', operate: "="},
                        {field: 'user.username', title: '用户名', operate: "LIKE"},
                        {field: 'user.real_name', title: '真实名称', operate: "LIKE"},
                        {field: 'bank_name', title: '所属银行', operate: "LIKE"},
                        {field: 'card_number', title: '卡号', operate: "LIKE"},
                        {field: 'account_bank_site', title: '开户行地址', operate: "LIKE"},
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                1: "正常",
                                0: "禁用",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status,
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
