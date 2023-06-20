<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:95:"D:\work\WAR_investment\code\public/../application/admin\view\project_config\app_config\add.html";i:1677221844;s:70:"D:\work\WAR_investment\code\application\admin\view\layout\default.html";i:1671020443;s:67:"D:\work\WAR_investment\code\application\admin\view\common\meta.html";i:1671020443;s:69:"D:\work\WAR_investment\code\application\admin\view\common\script.html";i:1671020443;}*/ ?>
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
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <?php echo token(); ?>

    <div class="form-group">
        <label for="c-name" class="control-label col-xs-12 col-sm-2">名称:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" data-rule="required" class="form-control" name="row[name]" type="text"
                   value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-value" class="control-label col-xs-12 col-sm-2">配置值:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-value" data-rule="required" class="form-control" name="row[value]" type="text"
                   value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-describe" class="control-label col-xs-12 col-sm-2">描述:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-describe" data-rule="" rows="4" class="form-control"
                      name="row[describe]"></textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="c-error_tips" class="control-label col-xs-12 col-sm-2">错误提示:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-error_tips" data-rule="" class="form-control" name="row[error_tips]" type="text"
                   value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-only_tag" class="control-label col-xs-12 col-sm-2">唯一标识:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-only_tag" data-rule="required" class="form-control" name="row[only_tag]" type="text"
                   value="">
        </div>
    </div>

    <div class="form-group">
        <label for="c-extend" class="control-label col-xs-12 col-sm-2">额外参数:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-extend" data-rule="" class="form-control" name="row[extend]" type="text"
                   value="">
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
