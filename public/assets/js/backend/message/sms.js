define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'message/sms/index',
                    del_url: 'message/sms/del',
                    table: 'sms'
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
                        {field: 'id', title: 'ID', sortable: true},
                        {
                            field: "event",
                            title: "事件",
                            searchList: {
                                'register': "注册",
                                'changemobile': "更换手机号",
                                'changepwd': "更换密码",
                                'resetpwd': "重置密码",
                                'resetSafePwd': "重置安全密码",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.label,
                            //custom: {1: "success"},
                        },
                        {field: 'mobile', title: '手机号', operate: 'LIKE'},
                        {field: 'code', title: '验证码', operate: false},
                        {field: 'times', title: '验证次数', operate: false},
                        {field: 'ip', title: 'IP地址', operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            // width: 150
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
