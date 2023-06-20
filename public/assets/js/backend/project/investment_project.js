define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project/investment_project/index',
                    add_url: 'project/investment_project/add',
                    edit_url: 'project/investment_project/edit',
                    del_url: 'project/investment_project/del',
                    multi_url: 'project/investment_project/multi',
                    // dragsort_url: 'ajax/weigh',
                    table: 'investment_project'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "project_id",
                sortName: "project_id",
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
                        {field: 'project_id', title: '项目id', sortable: true},
                        {field: 'project_type_id', title: '项目板块id', operate: '='},
                        {field: 'project_type.type_name', title: '项目板块名称', operate: false},
                        {field: 'name', title: '项目名称', operate: 'LIKE'},
                        {
                            field: 'head_img',
                            title: '项目封面图',
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'investment_project_label_name', title: '项目标签', width: 220, operate: false},
                        {field: 'buy_times_limit', title: '限购次数', operate: 'BETWEEN'},
                        {field: 'daily_rebates_rate', title: '日化利率(%)', operate: 'BETWEEN'},
                        {field: 'project_cycle', title: '项目周期(天)', operate: 'BETWEEN'},
                        {field: 'buy_min_number', title: '起投金额', operate: 'BETWEEN'},
                        {field: 'project_size', title: '项目规模', operate: 'BETWEEN'},
                        {field: 'funds_raised', title: '已募集资金', operate: 'BETWEEN'},
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
                        {field: 'month_rebate_daily', title: '月返指定日期', operate: 'BETWEEN'},
                        {field: 'recurring_rebate_interval_date', title: '定期返利间隔时间', operate: 'BETWEEN'},
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
                        {field: 'investment_risk', title: '投资风险提示', operate: 'LIKE'},
                        {field: 'guarantor_institutions', title: '担保机构', operate: 'LIKE'},
                        {field: 'project_detail', title: '项目详情', formatter: Table.api.formatter.content},
                        {field: 'project_information', title: '项目资料', formatter: Table.api.formatter.content},
                        {
                            field: 'can_use_coupons',
                            title: __('能否使用优惠券'),
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
                        },
                        {field: 'give_coupons_times', title: '赠送优惠券次数', operate: false},
                        {field: 'give_coupons_amount', title: '赠送优惠券金额', operate: false},
                        {field: 'give_coupons_can_use_project_type_name', title: '优惠券可使用项目板块', operate: false},
                        {field: 'give_coupons_use_limit', title: '优惠券可使用最低投资金额', operate: false},
                        {field: 'give_coupons_valid_days', title: '优惠券有效天数', operate: false},
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
                            title: __('有三级分销奖励（投资收益）'),
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
                            field: 'can_investment_distribution_rebate',
                            title: __('有三级分销奖励（投资本金）'),
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
                                1: __('启用'),
                                0: __('关闭')
                            },
                            custom: {
                                1: "success",
                                0: "danger"
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

            //操作日化利率
            $(document).on("click", ".btn-change_rebates_rate", function () {
                var tableData = table.bootstrapTable("getSelections")[0];
                if (tableData.length < 1) {
                    Layer.alert("请选择需要操作的项目");
                    return false;
                }
                Fast.api.open("project/investment_project/change_rebates_rate?id=" + tableData.project_id, "操作项目日化利率", {});
            });
        },
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[is_give_coupons]']").change(function () {
                if ($(this).val() == 1) {
                    $('#coupons_config').css('display', 'block');
                } else {
                    $('#coupons_config').hide();
                }
            });
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[is_give_coupons]']").change(function () {
                if ($(this).val() == 1) {
                    $('#coupons_config').css('display', 'block');
                } else {
                    $('#coupons_config').hide();
                }
            });
        },
        change_rebates_rate: function () {
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
