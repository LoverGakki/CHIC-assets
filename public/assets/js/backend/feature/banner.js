define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'feature/banner/index',
                    add_url: 'feature/banner/add',
                    edit_url: 'feature/banner/edit',
                    del_url: 'feature/banner/del',
                    multi_url: 'feature/banner/multi',
                    table: 'banner'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "banner_id",
                sortName: "banner_id",
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
                        {field: 'banner_id', title: '轮播图id', sortable: true},
                        {field: 'name', title: '名称', operate: "LIKE"},
                        {
                            field: 'image',
                            title: '图片',
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'url', title: '跳转地址', operate: "LIKE"},
                        {field: 'description', title: '详情',formatter: Table.api.formatter.content},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('禁用'), 1: __('启用')}
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
