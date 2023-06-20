define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project_config/app_config/index',
                    add_url: 'project_config/app_config/add',
                    edit_url: 'project_config/app_config/edit',
                    del_url: 'project_config/app_config/del',
                    multi_url: 'project_config/app_config/multi',
                    // dragsort_url: 'ajax/weigh',
                    table: 'app_config'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "config_id",
                sortName: "config_id",
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
                        {field: 'config_id', title: '配置id', sortable: true},
                        {field: 'name', title: '名称', operate: "LIKE", sortable: false},
                        {field: 'value', title: '配置值', operate: "LIKE"},
                        {field: 'describe', title: '描述', operate: "LIKE"},
                        {field: 'error_tips', title: '错误提示', operate: "LIKE"},
                        {field: 'only_tag', title: '唯一标识', operate: "LIKE"},
                        {field: 'extend', title: '额外参数', operate: "LIKE"},
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
