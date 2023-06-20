define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project_config/web_version/index',
                    // add_url: 'project_config/web_version/add',
                    edit_url: 'project_config/web_version/edit',
                    // del_url: 'project_config/web_version/del',
                    multi_url: 'project_config/web_version/multi',
                    // dragsort_url: 'ajax/weigh',
                    table: 'web_version'
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
                pageSize: 20, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [20, 50, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: 'Id', sortable: true},
                        {field: 'os_name', title: '平台名称', operate: "="},
                        {field: 'now_version', title: '当前版本', operate: "="},
                        {field: 'info', title: '简述', operate: "="},
                        {field: 'description', title: '详情描述', operate: "LIKE"},
                        {field: 'download_link', title: '下载地址', operate: "="},
                        {
                            field: "enforce",
                            title: "强制更新",
                            searchList: {
                                1: "是",
                                0: "否",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: "hot_update",
                            title: "热更新",
                            searchList: {
                                1: "是",
                                0: "否",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                1: "启用",
                                0: "禁用",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status
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
