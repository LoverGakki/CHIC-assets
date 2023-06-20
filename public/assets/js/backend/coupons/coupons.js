define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupons/coupons/index',
                    add_url: 'coupons/coupons/add',
                    edit_url: 'coupons/coupons/edit',
                    del_url: 'coupons/coupons/del',
                    multi_url: 'coupons/coupons/multi',
                    table: 'coupons'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "coupons_id",
                sortName: "coupons_id",
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
                        {field: 'coupons_id', title: '优惠券id', sortable: true},
                        {field: 'name', title: '名称', operate: "LIKE"},
                        {field: 'period', title: '期限', operate: 'BETWEEN'},
                        {
                            field: 'type',
                            title: __('优惠券类型'),
                            formatter: Table.api.formatter.label,
                            searchList: {1: __('现金券'), 2: __('加息券')}
                        },
                        {
                            field: 'coupons_number',
                            title: '金额（元）',
                            operate: 'BETWEEN',
                            formatter: function (value, rows) {
                                if (rows.type === 2) {
                                    return value + '%';
                                } else {
                                    return value + '元';
                                }
                            }
                        },
                        {field: 'coupons_use_limit', title: '可使用最低投资金额', operate: 'BETWEEN'},
                        {field: 'project_type_ids', title: '可使用项目板块id', operate: 'FIND_IN_SET',visible:false},
                        {field: 'project_type_name', title: '可使用项目板块', operate: false},
                        {field: 'remark', title: '备注', operate: false},
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
