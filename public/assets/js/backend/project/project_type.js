define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project/project_type/index',
                    add_url: 'project/project_type/add',
                    edit_url: 'project/project_type/edit',
                    del_url: 'project/project_type/del',
                    multi_url: 'project/project_type/multi',
                    // dragsort_url: 'ajax/weigh',
                    table: 'project_type'
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
                pageList: [10, 20, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: 'ID', sortable: true},
                        {field: 'type_name', title: '板块名称', operate: 'LIKE'},
                        {field: 'weigh', title: '排序', sortable: true},
                        /*{
                            field: 'is_give_coupons',
                            title: __('是否赠送优惠券'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('是'),
                                0: __('否')
                            },
                            custom: {
                                1: "success",
                                0: "primary"
                            }
                        },*/
                        // {field: 'label', title: '产品标签', operate: "FIND_IN_SET"},
                        // {field: 'give_coupons_can_use_type', title: '优惠券可使用板块', operate: false},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('启用'),
                                0: __('关闭')
                            },
                            custom: {
                                1: "success",
                                0: "primary"
                            }
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
