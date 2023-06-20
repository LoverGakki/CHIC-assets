<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:93:"D:\work\WAR_investment\code\public/../application/admin\view\coupons\issue_coupons\index.html";i:1685343666;s:70:"D:\work\WAR_investment\code\application\admin\view\layout\default.html";i:1671020443;s:67:"D:\work\WAR_investment\code\application\admin\view\common\meta.html";i:1671020443;s:69:"D:\work\WAR_investment\code\application\admin\view\common\script.html";i:1671020443;}*/ ?>
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
                                <div class="panel panel-default panel-intro">
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">

                    <div class="form-group">
                        <div class="col-xs-12 col-sm-2">
                            <input id="mobile" data-rule="required" class="form-control" placeholder="手机号码" type="text" value="">
                        </div>
                        <a href="javascript:;" class="btn btn-success btn-search-member" title="查找会员"> 查找会员</a>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12 col-sm-2">
                            <!--<input id="mobile" data-rule="required" class="form-control" placeholder="会员等级" type="text" value="">-->
                            <select name="level-config-id" id="level-config-id" class="form-control selectpicker">
                                <option value="">会员等级</option>
                                <?php if(is_array($user_level) || $user_level instanceof \think\Collection || $user_level instanceof \think\Paginator): if( count($user_level)==0 ) : echo "" ;else: foreach($user_level as $key=>$item): ?>
                                <option value="<?php echo $item['level_number']; ?>">V<?php echo $item['level_number']; ?>会员</option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <div class="col-xs-12 col-sm-2">
                            <!--<input id="coupons" data-rule="required" class="form-control" placeholder="选择优惠券" type="text" value="">-->
                            <select name="coupons" id="coupons" class="form-control selectpicker">
                                <option value="">请选择优惠券</option>
                                <?php if(is_array($coupons) || $coupons instanceof \think\Collection || $coupons instanceof \think\Paginator): if( count($coupons)==0 ) : echo "" ;else: foreach($coupons as $key=>$item): ?>
                                <option value="<?php echo $item['coupons_id']; ?>"><?php echo $item['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                        <a href="javascript:;" class="btn btn-success btn-issue-coupons" title="群发优惠券"> 群发优惠券</a>
                    </div>

                    <!--<a href="javascript:;" class="btn btn-info btn-change_rebates_rate btn-disabled disabled <?php echo $auth->check('project/investment_project/change_rebates_rate')?'':'hide'; ?>" title="操作日化利率"><i class="fa fa-money"></i> 操作日化利率</a>-->

                    <div id="toolbar" class="toolbar">
                        <?php echo build_toolbar('refresh'); ?>
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
