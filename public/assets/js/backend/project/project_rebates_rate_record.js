define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project/project_rebates_rate_record/index',
                    add_url: 'project/project_rebates_rate_record/add',
                    del_url: 'project/project_rebates_rate_record/del',
                    // dragsort_url: 'ajax/weigh',
                    table: 'project_rebates_rate_record'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "project_rebates_rate_id",
                sortName: "project_rebates_rate_id",
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
                        {field: 'project_rebates_rate_id', title: '记录id', sortable: true},
                        {field: 'project_id', title: '项目id', sortable: true},
                        {field: 'investment_project.name', title: '项目名称', sortable: true},
                        {field: 'investment_project.daily_rebates_rate', title: '项目设定日化利率(%)', sortable: true},
                        {field: 'daily_rebates_rate', title: '当前日化利率(%)', operate: 'BEWTEEN'},
                        {
                            field: 'start_time',
                            title: __('开始时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: 'end_time',
                            title: __('结束时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
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
