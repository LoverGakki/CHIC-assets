define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/extract_order/index',
                    edit_url: 'user/extract_order/edit',
                    table: 'extract_order'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "extract_order_id",
                sortName: "extract_order_id",
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
                        {field: 'extract_order_id', title: '提币id', sortable: true},
                        {field: 'user_id', title: '用户id', sortable: true},
                        {field: 'user.username', title: '用户名', operate: "LIKE"},
                        {field: 'order_code', title: '提币编号', operate: "LIKE"},
                        {field: 'extract_amount', title: '提取金额', operate: "BETWEEN"},
                        {field: 'extract_ratio', title: '提取手续费比例%', operate: "BETWEEN"},
                        {field: 'actual_into_value', title: '实际到账金额', operate: "BETWEEN"},
                        {field: 'receive_bank', title: '收款银行', operate: "LIKE"},
                        {field: 'receive_card_number', title: '收款银行卡号', operate: "LIKE"},
                        {field: 'receive_card_hold_name', title: '收款人姓名', operate: "LIKE"},
                        {field: 'receive_bank_site', title: '开户行地址', operate: "LIKE"},
                        {
                            field: "audit_status",
                            title: "是否审核",
                            searchList: {
                                0: "未审核",
                                1: "通过",
                                2: "不通过",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status
                        },

                        {field: 'audit_remark', title: '审核备注', operate: false},
                        {field: 'extract_voucher', title: '提现凭证', operate: false},

                        //1=申请中，2=转帐中，3=已完成，4=已取消
                        {
                            field: "status",
                            title: "状态",
                            searchList: {
                                1: "申请中",
                                2: "转账中",
                                3: "已完成",
                                4: "已取消",
                            },
                            operate: "=",
                            formatter: Table.api.formatter.status
                        },
                        {field: 'admin_id', title: '后台管理员id', operate: "="},
                        {field: 'remark', title: '备注', operate: "LIKE"},
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
                            field: 'processing_time',
                            title: '操作时间',
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: "buttons",
                            title: __("记录操作"),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: "extract_audit",
                                    text: __("审核操作"),
                                    title: __("申请审核"),
                                    classname: "btn btn-xs btn-success btn-click",
                                    click: function (index, data) {
                                        var extract_order_id = data.extract_order_id;
                                        var status = data.status;
                                        if (status !== 1) {
                                            Layer.alert("该申请已审核");
                                            return false;
                                        }

                                        layer.confirm(
                                            "是否通过?",
                                            {icon: 3, title: "提示", btn: ["通过", "不通过"]},
                                            function (index) {
                                                //先校验申请
                                                $.ajax({
                                                    type: "POST",
                                                    url: "user/extract_order/pass_verify",
                                                    data: {
                                                        record_id: extract_order_id,
                                                        status: 1,
                                                        audit_status: 1,
                                                    },
                                                    dataType: "json",
                                                    success: function (data) {
                                                        if (data.code != 1) {
                                                            Layer.alert(data.msg);
                                                            $(".btn-refresh").trigger("click");
                                                            return false;
                                                        }
                                                        $(".btn-refresh").trigger("click");
                                                        layer.close(index);
                                                        return true;
                                                    }
                                                });
                                            },
                                            function (index) {
                                                layer.prompt({
                                                    title: '审核不通过备注',
                                                    formType: 2
                                                }, function (remark, index_prompt) {
                                                    layer.close(index_prompt);
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "user/extract_order/pass_verify",
                                                        data: {
                                                            record_id: extract_order_id,
                                                            status: 1,
                                                            audit_status: 2,
                                                            remark: remark,
                                                        },
                                                        dataType: "json",
                                                        success: function (data) {
                                                            Layer.alert(data.msg);
                                                            $(".btn-refresh").trigger("click");
                                                            layer.close(index);
                                                        }
                                                    });
                                                });
                                            }
                                        );
                                    },
                                    visible: function (row) {
                                        return row.status === 1 && row.audit_status == 0;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons,
                            operate: false
                        },
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
