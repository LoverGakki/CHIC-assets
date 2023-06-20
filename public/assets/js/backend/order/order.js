define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order/index',
                    table: 'order'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "order_id",
                sortName: "order_id",
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
                        {field: 'order_id', title: '订单id', sortable: true},
                        {field: 'user_id', title: '用户id', sortable: false},
                        {field: 'user.username', title: '用户名', operate: 'LIKE'},
                        {field: 'project_id', title: '项目id', operate: '='},
                        {field: 'project_name', title: '项目名称', operate: 'LIKE'},
                        {field: 'order_code', title: '订单编号', operate: 'LIKE'},
                        {field: 'investment_amount', title: '投资金额', operate: 'BETWEEN'},
                        {field: 'actual_pay_investment_amount', title: '实际支付金额', operate: 'BETWEEN'},
                        {field: 'user_coupons_id', title: '优惠券id', operate: '='},
                        {field: 'coupons_amount', title: '优惠金额', operate: 'BETWEEN'},
                        {field: 'daily_rebates_rate', title: '日化利率(%)', operate: 'BETWEEN'},
                        {field: 'period', title: '持有天数', operate: 'BETWEEN'},
                        {
                            field: 'rebate_method',
                            title: '返利方式',
                            formatter: Table.api.formatter.label,
                            searchList: {
                                1: __('日返'),
                                2: __('月返'),
                                3: __('定期返'),
                            },
                            custom: {
                                1: "success",
                                2: "primary",
                                3: "info",
                            }
                        },
                        {
                            field: 'dividend_method',
                            title: '分红方式',
                            formatter: Table.api.formatter.label,
                            searchList: {
                                1: __('本息同返'),
                                2: __('先息后本'),
                                3: __('本息定期同返'),
                            },
                            custom: {
                                1: "success",
                                2: "primary",
                                3: "info",
                            }
                        },
                        {field: 'month_rebate_daily', title: '月返指定日期', operate: 'BETWEEN'},
                        {field: 'recurring_rebate_interval_date', title: '定期返利间隔时间', operate: 'BETWEEN'},
                        {field: 'already_periods', title: '已释放天数', operate: 'BETWEEN'},
                        {
                            field: 'is_expired',
                            title: __('是否到期'),
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
                        {field: 'output', title: '已产出收益', operate: 'BETWEEN'},
                        {field: 'already_return_principal', title: '已返还本金', operate: 'BETWEEN'},
                        {
                            field: 'profit_rollover_model',
                            title: __('利滚利模式'),
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
                            field: 'can_change_rebates_rate',
                            title: __('能否修改日化利率'),
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
                            field: 'can_distribution_rebate',
                            title: __('有三级分销奖励'),
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
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('收益中'),
                                2: __('已结束'),
                                3: __('已取消'),
                            },
                            custom: {
                                1: "success",
                                2: "primary",
                                3: "danger",
                            }
                        },
                        {field: 'remark', title: '备注', operate: false},
                        {
                            field: 'token_pay_time',
                            title: __('支付时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: 'next_release_time',
                            title: __('下次释放时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            width: 150
                        },
                        {
                            field: 'expired_time',
                            title: __('到期时间'),
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
