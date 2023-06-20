define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_login_log/index',
                    del_url: 'user/user_login_log/del',
                    table: 'user_login_log'
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
                        {field: 'id', title: 'ID', operate: "=", sortable: true},
                        {field: 'user_id', title: '用户id', operate: "="},
                        {field: 'user.username', title: '用户名', operate: "LIKE"},
                        {
                            field: 'prevtime',
                            title: __('上次登录时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                        },
                        {field: 'loginip', title: '登录ip', operate: "LIKE"},
                        {
                            field: 'logintime',
                            title: __('登录时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
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
