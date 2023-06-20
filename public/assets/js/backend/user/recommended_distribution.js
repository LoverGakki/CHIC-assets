define(['jquery', 'bootstrap', 'backend', 'form', 'table', 'bootstrap-treeview'], function ($, undefined, Backend, Form, Table, Treeview) {
    var treeDom = $('#tree');

    //获取直推下级数据
    function getTreeData(mobile) {
        var tree = [];
        $.ajax({
            type: "POST",
            url: "user/recommended_distribution/get_children_tree",
            data: {
                mobile: mobile,
            },
            dataType: "json",
            async: false,
            success: function (dat) {
                if (dat.code === 1) {
                    tree = dat.data;
                    /*$.each(dat.data, function (k, v) {
                        tree.push({
                            text: v,
                            icon: "glyphicon glyphicon-user",
                            selectedIcon: "glyphicon glyphicon-user",
                        });
                    });*/
                    //$('#tree').treeview({data: tree});
                    //tree = tree;
                }
            }
        });
        return tree;
    }

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/recommended_distribution/index',
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
                    /*var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    var mobile = $('#mobile').val();
                    params.mobile = mobile;
                    if (mobile.length > 0) {
                        filter.mobile = mobile;
                        op.mobile = "LIKE";
                    }
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;*/
                    params.mobile = $('#mobile').val();
                    return params;
                },
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: '用户ID', operate: "=", sortable: true},
                        {field: 'invite_code', title: '推荐码', operate: "="},
                        {field: 'mobile', title: '账号', operate: "LIKE"},
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
                        {field: 'real_name', title: '姓名', operate: false},
                        {
                            field: 'logintime',
                            title: __('登录时间'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                        },
                        {field: 'invest_total_amount', title: '累计投资金额', operate: "BETWEEN"},
                        {field: 'cumulative_recharge_amount', title: '累计充值金额', operate: "BETWEEN"},
                        {field: 'cumulative_withdrawals_amount', title: '累计提现金额', operate: "BETWEEN"},
                        {field: 'token_value', title: '余额', operate: "BETWEEN"},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')}
                        },
                        /*{
                            field: 'token_value', title: '推荐人', operate: "LIKE", formatter: function (value, row) {
                                if (row.superior_user) {
                                    return row.superior_user.invite_code + ' | ' + row.superior_user.mobile;
                                } else {
                                    return '';
                                }

                            }
                        },*/
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
                    //table.bootstrapTable("refresh");
                    return false;
                }
                //先校验申请
                $.ajax({
                    type: "POST",
                    url: "user/recommended_distribution/recommend_relationship",
                    data: {
                        mobile: mobile,
                    },
                    dataType: "json",
                    success: function (dat) {
                        if (dat.code != 1) {
                            Layer.alert(dat.msg);
                            return false;
                        }
                        var data = dat.data;
                        var upper_str = '';
                        if (data.upper_user_data) {
                            $.each(data.upper_user_data, function (k, v) {
                                if (k === 0) {
                                    upper_str += '<span style="color: red">' + v.mobile + '（V' + v.role_level + '会员）</span>'
                                } else {
                                    upper_str += '<span>' + v.mobile + '（V' + v.role_level + '会员）</span>'
                                }
                                if (data.upper_user_data.length - 1 !== k) {
                                    upper_str += '<img style="width: 30px;height: 30px" src="/static/img/left_arrows.png" alt="">';
                                }
                            });
                        }

                        $(".recommend-relationship").html('<div style="font-size: 20px;margin-top: 15px">推荐关系</div><div style="font-size: 16px;margin-top: 15px;margin-bottom: 15px;">' + upper_str + '</div><div>当前用户id：' + data.user_data.id + '<br>手机号：' + data.user_data.mobile + '</div><div style="font-size: 16px;margin-top: 15px">当前推荐表（下级）</div>');

                        $(".btn-refresh").trigger("click");
                        return true;
                    }
                });
                //table.bootstrapTable("refresh");

                var treeData = getTreeData(mobile);
                var nodeId = $('#tree .list-group').find("*").eq(0).attr('data-nodeid');
                treeDom.treeview("deleteChildrenNode", Number(nodeId));	//删除当前节点下的所有子节点
                //根据返回的数据源，添加子节点
                for (var i = 0; i < treeData.length; i++) {
                    //result[i]的格式如下图
                    treeDom.treeview("addNode", [Number(nodeId), {node: treeData[i], silent: true}]);
                }
                treeDom.treeview('expandNode', [Number(nodeId), {silent: true}]);

            });

            var tree = [
                {
                    text: "Parent 1",
                    icon: "glyphicon glyphicon-user",
                    selectedIcon: "glyphicon glyphicon-user",
                    nodes: [
                        {
                            text: "Child 1",
                            nodes: [
                                {
                                    text: "Grandchild 1"
                                },
                                {
                                    text: "Grandchild 2"
                                }
                            ]
                        },
                        {
                            text: "Child 2",
                        }
                    ]
                },
                {
                    text: "Parent 2",
                    nodes: []
                },
                {
                    text: "Parent 3",
                    nodes: []
                },
                {
                    text: "Parent 4",
                    nodes: []
                },
                {
                    text: "Parent 5"
                }
            ];
            // $('#tree').treeview({data: []});
            treeDom.treeview({
                //data: getTreeData(''),
                data: [
                    {
                        text: "",
                        icon: "glyphicon glyphicon-user",
                        selectedIcon: "glyphicon glyphicon-user",
                        nodes: []
                    }
                ],
                showTags: true,
                enableLinks: true,
                levels: 2,
            });

            //treeview节点展开事件
            treeDom.on('nodeExpanded', function (event, data) {
                var id = data['nodeId'];		//获取节点的nodeid （nodeid是treeview自动生成的，每个节点不同）
                var treeData = getTreeData(data['text']);
                if (treeData.length > 0) {
                    $('#tree').treeview("deleteChildrenNode", id);	//删除当前节点下的所有子节点
                    //根据返回的数据源，添加子节点
                    for (var i = 0; i < treeData.length; i++) {
                        //result[i]的格式如下图
                        $('#tree').treeview("addNode", [id, {node: treeData[i], silent: true}]);
                    }
                }

                /*$.ajax({					//异步加载当前节点的子节点数据
                    type: "post",
                    url: "/GuideSeach/InitIPCTreeViewByFatherID",
                    async: false,
                    dataType: 'json',
                    data: {
                        PIPC: data['text']
                    },
                    beforeSend: function () {
                        layer.load();
                    },
                    complete: function () {
                        layer.closeAll('loading'); //关闭loading
                    },
                    success: function (result) {
                        //再添加节点前，需要清空展开节点下的子节点，否则会累计很多节点。
                        $("#NationalPatent").treeview("deleteChildrenNode", id);	//删除当前节点下的所有子节点
                        //根据返回的数据源，添加子节点
                        for (var i = 0; i < result.length; i++) {
                            //result[i]的格式如下图
                            $("#NationalPatent").treeview("addNode", [id, {node: result[i], silent: true}
                            ]);
                        }
                    }
                })*/
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
