define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'feature/news/index',
                    add_url: 'feature/news/add',
                    edit_url: 'feature/news/edit',
                    del_url: 'feature/news/del',
                    multi_url: 'feature/news/multi',
                    table: 'news'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "news_id",
                sortName: "news_id",
                sortOrder: "desc",
                method: "GET",
                search: true,
                commonSearch: true,
                showToggle: true,
                showRefresh: true,
                pagination: true, // 启动分页
                pageSize: 5, // 每页显示的记录数
                pageNumber: 1, // 当前第几页
                pageList: [5, 10, "All"], // 记录数可选列表
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'news_id', title: '新闻id', sortable: true},
                        {field: 'name', title: '名称', operate: "LIKE"},
                        {field: 'brief_introduction', title: '简介', operate: false},
                        {
                            field: 'cover_image',
                            title: '封面图',
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {
                            field: 'video',
                            title: '视频',
                            formatter: function (value, rows) {
                                if (value && value.length > 0 && rows.content_type == 2) {
                                    return '<video controls preload="none" style="height:190px;width:336px;" poster="' + rows.cover_image + '"><source src="' + value + '" /></video>';
                                } else {
                                    return '';
                                }
                            },
                            operate: false
                        },
                        {field: 'content', title: '详情', formatter: Table.api.formatter.content},
                        {
                            field: 'news_type',
                            title: __('新闻类型'),
                            formatter: Table.api.formatter.label,
                            searchList: {1: __('新闻'), 2: __('视频'), 3: __('行业')}
                        },
                        {
                            field: 'content_type',
                            title: __('内容类型'),
                            formatter: Table.api.formatter.label,
                            searchList: {1: __('文本'), 2: __('视频')}
                        },
                        {
                            field: 'is_headlines',
                            title: __('是否为头条'),
                            formatter: Table.api.formatter.status,
                            searchList: {0: __('否'), 1: __('是')}
                        },
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
