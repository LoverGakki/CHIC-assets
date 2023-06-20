define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupons/issue_coupons/index',
                    table: 'user'
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
                search: false,
                commonSearch: true,
                showToggle: false,
                showRefresh: true,
                pagination: true, // 启动分页
                pageSize: 10, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [10, 50, "All"], // 记录数可选列表
                searchFormVisible: false,
                queryParams: function (params) {
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    var mobile = $('#mobile').val();
                    params.mobile = mobile;
                    if (mobile.length > 0) {
                        filter.mobile = mobile;
                        op.mobile = "LIKE";
                    }
                    var role_level = $('#level-config-id').val();
                    params.role_level = role_level;
                    if (role_level.length > 0) {
                        filter.role_level = role_level;
                        op.role_level = "=";
                    }
                    filter.status = 'normal';
                    op.status = '=';
                    filter.is_audit = 1;
                    op.is_audit = '=';
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: '用户ID', sortable: true},
                        {field: 'real_name', title: '姓名', operate: "LIKE"},
                        {field: 'mobile', title: '手机号', operate: false},
                        {field: 'role_level', title: '会员等级', operate: false},
                        {
                            field: 'is_audit',
                            title: __('是否审核'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('已通过'), 2: '未通过'},
                            operate: false
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')},
                            operate: false
                        },
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $("button[name='commonSearch']").hide();

            //查找会员
            $(document).on("click", ".btn-search-member", function () {
                var mobile = $('#mobile').val();
                if (mobile.length <= 0) {
                    Layer.alert("请输入手机号码");
                    table.bootstrapTable("refresh");
                    return false;
                }
                table.bootstrapTable("refresh");
            });

            //群发优惠券
            $(document).on("click", ".btn-issue-coupons", function () {
                var mobile = $('#mobile').val();
                var level_number = $('#level-config-id').val();
                if (mobile.length <= 0 && level_number.length <= 0) {
                    Layer.alert("请输入手机号码或选择会员等级");
                    return false;
                }
                var coupons = $('#coupons').val();
                if (coupons.length <= 0) {
                    Layer.alert("请选择要发放的优惠券");
                    return false;
                }

                var content = '';
                if (mobile.length > 0 && level_number.length <= 0) {
                    content = '是否给号码为' + mobile + '的会员发放优惠券?';
                }
                if (level_number.length > 0 && mobile.length <= 0) {
                    content = '是否给等级为' + level_number + '的会员发放优惠券?';
                }
                if (mobile.length > 0 && level_number.length > 0) {
                    content = '是否给号码为' + mobile + '的会员以及等级为' + level_number + '的会员发放优惠券?';
                }
                layer.confirm(
                    content,
                    {icon: 3, title: "提示", btn: ["是", "否"]},
                    function (index) {
                        //先校验申请
                        $.ajax({
                            type: "POST",
                            url: "coupons/issue_coupons/group_issue_coupons",
                            data: {
                                mobile: mobile,
                                role_level: level_number,
                                coupons: coupons,
                            },
                            dataType: "json",
                            success: function (data) {
                                Layer.alert(data.msg);
                                if (data.code != 1) {
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
                        layer.close(index);
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
