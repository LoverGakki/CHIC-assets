<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:97:"D:\work\WAR_investment\code\public/../application/admin\view\project\investment_project\edit.html";i:1686974289;s:70:"D:\work\WAR_investment\code\application\admin\view\layout\default.html";i:1671020443;s:67:"D:\work\WAR_investment\code\application\admin\view\common\meta.html";i:1671020443;s:69:"D:\work\WAR_investment\code\application\admin\view\common\script.html";i:1671020443;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <?php echo token(); ?>

    <div class="form-group">
        <label for="c-project_type_id" class="control-label col-xs-12 col-sm-2">所属项目板块:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-project_type_id" data-rule="required" data-source="project/project_type/selectpage"
                   data-field="type_name" data-search-field="type_name" data-primary-key="id" data-pagination="true"
                   data-order-by="id" data-multiple="false" data-params='{"custom[status]":"1"}'
                   class="form-control selectpage" name="row[project_type_id]" type="text" value="<?php echo $row['project_type_id']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-name" class="control-label col-xs-12 col-sm-2">项目名称:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" data-rule="required" class="form-control" name="row[name]" type="text" value="<?php echo $row['name']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-head_img" class="control-label col-xs-12 col-sm-2">项目封面图:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-head_img" data-rule="required" class="form-control" size="35" name="row[head_img]"
                       type="text" value="<?php echo $row['head_img']; ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-image" class="btn btn-danger faupload"
                                  data-input-id="c-head_img"
                                  data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp"
                                  data-multiple="false" data-preview-id="p-image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-image" class="btn btn-primary fachoose"
                                  data-input-id="c-head_img" data-mimetype="image/*" data-multiple="false"><i
                            class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-image"></ul>
        </div>
    </div>

    <div class="form-group">
        <label for="c-label_ids" class="control-label col-xs-12 col-sm-2">项目标签:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-label_ids" data-rule="" data-source="project/project_label/selectpage"
                   data-field="label_name" data-search-field="label_name" data-primary-key="label_id" data-pagination="true"
                   data-order-by="label_id" data-multiple="true" data-params='{"custom[status]":"1"}'
                   class="form-control selectpage" name="row[label_ids]" type="text" value="<?php echo $labelIds; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-buy_times_limit" class="control-label col-xs-12 col-sm-2">限投次数:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-buy_times_limit" data-rule="required" class="form-control" name="row[buy_times_limit]"
                   type="number" value="<?php echo $row['buy_times_limit']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-daily_rebates_rate" class="control-label col-xs-12 col-sm-2">日化利率(%):</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-daily_rebates_rate" data-rule="required" class="form-control" name="row[daily_rebates_rate]"
                   type="number" value="<?php echo $row['daily_rebates_rate']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-project_cycle" class="control-label col-xs-12 col-sm-2">项目周期(天):</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-project_cycle" data-rule="required" class="form-control" name="row[project_cycle]"
                   type="number" value="<?php echo $row['project_cycle']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-buy_min_number" class="control-label col-xs-12 col-sm-2">起投金额:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-buy_min_number" data-rule="required" class="form-control" name="row[buy_min_number]"
                   type="number" value="<?php echo $row['buy_min_number']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-buy_max_number" class="control-label col-xs-12 col-sm-2">限投金额:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-buy_max_number" data-rule="required" class="form-control" name="row[buy_max_number]"
                   type="number" value="<?php echo $row['buy_max_number']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-project_size" class="control-label col-xs-12 col-sm-2">项目规模:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-project_size" data-rule="required" class="form-control" name="row[project_size]" type="number"
                   value="<?php echo $row['project_size']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">返利方式:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[rebate_method]', ['1'=>__('日返'), '2'=>__('月返'), '3'=>__('定期返')], $row['rebate_method']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="c-month_rebate_daily" class="control-label col-xs-12 col-sm-2">月返指定日期:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-month_rebate_daily" data-rule="" class="form-control" name="row[month_rebate_daily]"
                   type="number" value="<?php echo $row['month_rebate_daily']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-recurring_rebate_interval_date" class="control-label col-xs-12 col-sm-2">定期返间隔时间:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-recurring_rebate_interval_date" data-rule="" class="form-control"
                   name="row[recurring_rebate_interval_date]" type="number" value="<?php echo $row['recurring_rebate_interval_date']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">分红方式:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[dividend_method]', ['1'=>__('本息同返'), '2'=>__('先息后本'), '3'=>__('本息定期同返')], $row['dividend_method']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="c-investment_risk" class="control-label col-xs-12 col-sm-2">投资风险提示语:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-investment_risk" data-rule="required" class="form-control" name="row[investment_risk]"
                   type="text" value="<?php echo $row['investment_risk']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-guarantor_institutions" class="control-label col-xs-12 col-sm-2">担保机构:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-guarantor_institutions" data-rule="required" class="form-control"
                   name="row[guarantor_institutions]"
                   type="text" value="<?php echo $row['guarantor_institutions']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="project_detail" class="control-label col-xs-12 col-sm-2">项目详情:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea class="form-control editor" data-rule="required" id="project_detail"
                      name="row[project_detail]"><?php echo $row['project_detail']; ?></textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="project_information" class="control-label col-xs-12 col-sm-2">项目资料:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea class="form-control editor" data-rule="required" id="project_information"
                      name="row[project_information]"><?php echo $row['project_information']; ?></textarea>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">能否使用优惠券:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_use_coupons]', ['1'=>__('能'), '0'=>__('不能')], $row['can_use_coupons']); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">是否赠送优惠券:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[is_give_coupons]', ['0'=>__('否'), '1'=>__('是')], $row['is_give_coupons']); ?>
        </div>
    </div>

    <!--优惠券配置-->
    <div id="coupons_config" style="display: <?php if($row['is_give_coupons'] == '1'): ?>block<?php else: ?>none<?php endif; ?>">
        <div class="form-group">
            <label for="c-give_coupons_times" class="control-label col-xs-12 col-sm-2">赠送优惠券次数:</label>
            <div class="col-xs-12 col-sm-8">
                <input id="c-give_coupons_times" data-rule="" class="form-control" name="row[give_coupons_times]" type="number" value="<?php echo $row['give_coupons_times']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="c-give_coupons_amount" class="control-label col-xs-12 col-sm-2">赠送优惠券金额:</label>
            <div class="col-xs-12 col-sm-8">
                <input id="c-give_coupons_amount" data-rule="" class="form-control" name="row[give_coupons_amount]" type="number" value="<?php echo $row['give_coupons_amount']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="c-give_coupons_can_use_project_type" class="control-label col-xs-12 col-sm-2">优惠券可使用项目板块:</label>
            <div class="col-xs-12 col-sm-8">
                <input id="c-give_coupons_can_use_project_type" data-rule="" data-source="project/project_type/selectpage"
                       data-field="type_name" data-search-field="type_name" data-primary-key="id" data-pagination="true"
                       data-order-by="id" data-multiple="false" data-params='{"custom[status]":"1"}'
                       class="form-control selectpage" name="row[give_coupons_can_use_project_type]" type="text" value="<?php echo $row['give_coupons_can_use_project_type']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="c-give_coupons_use_limit" class="control-label col-xs-12 col-sm-2">优惠券可使用最低投资金额:</label>
            <div class="col-xs-12 col-sm-8">
                <input id="c-give_coupons_use_limit" data-rule="" class="form-control" name="row[give_coupons_use_limit]" type="number" value="<?php echo $row['give_coupons_use_limit']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="c-give_coupons_valid_days" class="control-label col-xs-12 col-sm-2">优惠券有效天数:</label>
            <div class="col-xs-12 col-sm-8">
                <input id="c-give_coupons_valid_days" data-rule="" class="form-control" name="row[give_coupons_valid_days]" type="number" value="<?php echo $row['give_coupons_valid_days']; ?>">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">利滚利模式:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[profit_rollover_model]', [ '0'=>__('否'), '1'=>__('是')], $row['profit_rollover_model']); ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">能否修改日化利率:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_change_rebates_rate]', ['0'=>__('不能'), '1'=>__('能')], $row['can_change_rebates_rate']); ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">是否有三级分销奖励（投资收益）:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_distribution_rebate]', ['0'=>__('否'), '1'=>__('是')], $row['can_distribution_rebate']); ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">是否有三级分销奖励（投资本金）:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_investment_distribution_rebate]', [ '0'=>__('否'), '1'=>__('是')], $row['can_investment_distribution_rebate']); ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[status]', ['1'=>__('启用'), '0'=>__('关闭')], $row['status']); ?>
        </div>
    </div>

    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
