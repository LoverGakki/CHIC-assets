define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order_release_record/index',
                    table: 'order_release_record'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "record_id",
                sortName: "record_id",
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
                        {field: 'record_id', title: '记录id', sortable: true},
                        {field: 'order_id', title: '订单id', sortable: true},
                        {field: 'user_id', title: '用户id', sortable: false},
                        {field: 'user.username', title: '用户名', operate: 'LIKE'},
                        {field: 'release_number', title: '返利金额', operate: 'BETWEEN'},
                        {field: 'release_ratio', title: '返利比例(%)', operate: 'BETWEEN'},
                        {field: 'release_periods', title: '释放期数', operate: 'BETWEEN'},
                        {field: 'old_number', title: '旧总返利金额', operate: 'BETWEEN'},
                        {field: 'new_number', title: '新总返利金额', operate: 'BETWEEN'},
                        {field: 'return_principal', title: '返还本金', operate: 'BETWEEN'},
                        {
                            field: 'is_return_interest',
                            title: __('是否返还利息'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('是'),
                                0: __('否')
                            },
                            custom: {
                                1: "success",
                                0: "primary"
                            }
                        },
                        {
                            field: 'is_return_principal',
                            title: __('是否返还本金'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('能'),
                                0: __('不能')
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
                        /*{
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }*/
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
