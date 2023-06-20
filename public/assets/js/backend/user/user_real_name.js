define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_real_name/index',
                    table: 'user'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "id",
                sortName: "apply_time",
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
                        {field: 'id', title: '会员ID', sortable: true},
                        {field: 'mobile', title: '手机号', operate: "LIKE"},
                        {field: 'real_name', title: '姓名', operate: "="},
                        {field: 'id_card_number', title: '身份证号', operate: false},
                        {
                            field: 'id_card_front',
                            title: __('身份证正面照片'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {
                            field: 'id_card_back',
                            title: __('身份证反面照片'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {
                            field: 'apply_time',
                            title: '时间',
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        //状态
                        {
                            field: 'is_audit',
                            title: __('状态'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('未审核'), 1: __('已通过'), 2: '不通过'}
                        },
                        {field: 'audit_remark', title: '审核备注', operate: false},
                        {
                            field: "buttons",
                            title: __("操作"),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: "extract_audit",
                                    text: __("审核操作"),
                                    title: __("实名认证审核"),
                                    classname: "btn btn-xs btn-success btn-click",
                                    click: function (index, data) {
                                        var user_id = data.id;
                                        var is_audit = data.is_audit;
                                        if (is_audit === 1) {
                                            Layer.alert("实名认证已审核通过");
                                            return false;
                                        }

                                        layer.confirm(
                                            "是否通过?",
                                            {icon: 3, title: "提示", btn: ["通过", "不通过"]},
                                            function (index) {
                                                //先校验申请
                                                $.ajax({
                                                    type: "POST",
                                                    url: "user/user_real_name/real_name_audit",
                                                    data: {
                                                        user_id: user_id,
                                                        is_audit: 1,
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
                                                        url: "user/user_real_name/real_name_audit",
                                                        data: {
                                                            user_id: user_id,
                                                            is_audit: 2,
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
                                        return row.status === 'normal' && row.is_audit != 1;
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

            //批量实名审核
            $(document).on("click", ".btn-bulk-auditing", function () {
                var tableData = table.bootstrapTable("getSelections");
                if (tableData.length < 1) {
                    Layer.alert("请选择需要审核的用户");
                    return false;
                }
                //选中项的prod_id字符串
                var chooseIdStr = "";
                $.each(tableData, function (i, v) {
                    chooseIdStr += v.id + ",";
                });

                layer.confirm(
                    "是否通过?",
                    {icon: 3, title: "提示", btn: ["通过", "不通过"]},
                    function (index) {
                        //先校验申请
                        $.ajax({
                            type: "POST",
                            url: "user/user_real_name/real_name_bulk_audit",
                            data: {
                                user_ids: chooseIdStr,
                                is_audit: 1,
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
                                url: "user/user_real_name/real_name_bulk_audit",
                                data: {
                                    user_ids: chooseIdStr,
                                    is_audit: 2,
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
            });
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
