<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:112:"D:\work\WAR_investment\code\public/../application/admin\view\project\investment_project\change_rebates_rate.html";i:1684410619;s:70:"D:\work\WAR_investment\code\application\admin\view\layout\default.html";i:1671020443;s:67:"D:\work\WAR_investment\code\application\admin\view\common\meta.html";i:1671020443;s:69:"D:\work\WAR_investment\code\application\admin\view\common\script.html";i:1671020443;}*/ ?>
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
                                <form id="change_rebates_rate-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <?php echo token(); ?>

    <div class="form-group">
        <label for="c-project_id" class="control-label col-xs-12 col-sm-2">项目id:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-project_id" class="form-control" readonly="readonly" type="text" value="<?php echo $row['project_id']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-name" class="control-label col-xs-12 col-sm-2">项目名称:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" class="form-control" readonly="readonly" type="text" value="<?php echo $row['name']; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-daily_rebates_rate" class="control-label col-xs-12 col-sm-2">现日化利率(%):</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-daily_rebates_rate" class="form-control" type="text" readonly="readonly" value="<?php echo $now_rebates_rate; ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="c-new_rebates_rate" class="control-label col-xs-12 col-sm-2">新日化利率(%):</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-new_rebates_rate" data-rule="required" class="form-control" name="row[new_rebates_rate]" type="number" value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-start_time" class="control-label col-xs-12 col-sm-2">开始时间:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-start_time" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[start_time]" type="text" value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-end_time" class="control-label col-xs-12 col-sm-2">结束时间:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-end_time" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[end_time]" type="text" value="">
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
