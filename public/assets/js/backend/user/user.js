define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    // del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'superior_user_id', title: __('上级用户id'), sortable: true},
                        {field: 'invite_code', title: __('邀请码'), sortable: true},
                        // {field: 'group.name', title: __('Group')},
                        // {field: 'username', title: __('Username'), operate: 'LIKE'},
                        // {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'real_name', title: __('真实姓名'), operate: '='},
                        {field: 'id_card_number', title: __('身份证号'), operate: 'LIKE'},
                        {
                            field: 'role_level',
                            title: __('用户等级'),
                            operate: '=',
                            formatter: Table.api.formatter.label,
                            searchList: {
                                0: __('V0'),
                                1: __('V1'),
                                2: __('V2'),
                                3: __('V3'),
                                4: __('V4'),
                                5: __('V5'),
                                6: __('V6'),
                            }
                        },
                        {field: 'token_value', title: __('用户余额'), operate: 'BETWEEN', sortable: true},
                        {field: 'invest_total_amount', title: __('总投资金额'), operate: 'BETWEEN', sortable: true},
                        {field: 'cumulative_earnings_value', title: __('累计收益金额'), operate: 'BETWEEN'},
                        {field: 'direct_drive_number', title: __('直推人数'), operate: 'BETWEEN'},
                        {field: 'valid_direct_drive_number', title: __('有效直推人数'), operate: 'BETWEEN'},
                        {field: 'team_number', title: __('团队人数'), operate: 'BETWEEN'},
                        {field: 'team_performance', title: __('团队业绩'), operate: 'BETWEEN'},
                        {
                            field: 'is_valid',
                            title: __('有效用户'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('是')}
                        },
                        {
                            field: 'is_activated',
                            title: __('激活用户'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('是')}
                        },
                        {
                            field: 'is_audit',
                            title: __('是否审核'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('已通过'), 2: '未通过'}
                        },
                        {field: 'audit_remark', title: __('审核备注'), operate: false},

                        {
                            field: 'can_transfer',
                            title: __('能否转账'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('能')}
                        },
                        {
                            field: 'can_extract',
                            title: __('能否提现'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('能')}
                        },
                        {
                            field: 'can_get_rebates',
                            title: __('能否获取返利'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('能')}
                        },
                        {
                            field: 'can_get_dividends',
                            title: __('能否获取分红'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('能')}
                        },
                        {
                            field: 'can_login',
                            title: __('能否登录'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('能')}
                        },

                        // {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        // {field: 'level', title: __('Level'), operate: 'BETWEEN', sortable: true},
                        // {field: 'gender', title: __('Gender'), visible: false, searchList: {1: __('Male'), 0: __('Female')}},
                        // {field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true},
                        // {field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true},
                        // {field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true},
                        // {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        // {field: 'loginip', title: __('Loginip'), formatter: Table.api.formatter.search},
                        {
                            field: 'jointime',
                            title: __('Jointime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        // {field: 'joinip', title: __('Joinip'), formatter: Table.api.formatter.search},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')}
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //操作余额
            $(document).on("click", ".btn-change-token-value", function () {
                var tableData = table.bootstrapTable("getSelections")[0];
                if (tableData.length < 1) {
                    Layer.alert("请选择需要操作的用户");
                    return false;
                }
                Fast.api.open("user/user/change_token_value?id=" + tableData.id, "操作用户余额", {});
            });

            //发放优惠券
            $(document).on("click", ".btn-grant-discount-coupon", function () {
                var tableData = table.bootstrapTable("getSelections")[0];
                if (tableData.length < 1) {
                    Layer.alert("请选择需要操作的用户");
                    return false;
                }
                Fast.api.open("user/user/grant_discount_coupon?id=" + tableData.id, "发放优惠券", {});
            });

            $(document).on("click", ".btn-view-password", function () {
                var tableData = table.bootstrapTable("getSelections")[0];
                if (tableData.length < 1) {
                    Layer.alert("请选择需要操作的用户");
                    return false;
                }
                $(".btn-refresh").trigger("click");
                Layer.alert('手机号：' + tableData.mobile + '<br/>登录密码：' + tableData.password_text);
                return true;
            });

            $(document).on("click", ".btn-balance-transfer", function () {
                var tableData = table.bootstrapTable("getSelections")[0];
                if (tableData.length < 1) {
                    Layer.alert("请选择需要操作的用户");
                    return false;
                }
                Fast.api.open("user/user/balance_transfer?id=" + tableData.id, "操作用户转账", {});
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        change_token_value: function () {
            Controller.api.bindevent();
        },
        grant_discount_coupon: function () {
            Controller.api.bindevent();
        },
        balance_transfer: function () {
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