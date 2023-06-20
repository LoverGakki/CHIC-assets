<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:80:"D:\work\WAR_investment\code\public/../application/admin\view\user\user\edit.html";i:1687097759;s:70:"D:\work\WAR_investment\code\application\admin\view\layout\default.html";i:1671020443;s:67:"D:\work\WAR_investment\code\application\admin\view\common\meta.html";i:1671020443;s:69:"D:\work\WAR_investment\code\application\admin\view\common\script.html";i:1671020443;}*/ ?>
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
    <input type="hidden" name="row[id]" value="<?php echo $row['id']; ?>">
    <!--<div class="form-group">
        <label for="c-group_id" class="control-label col-xs-12 col-sm-2"><?php echo __('Group'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <?php echo $groupList; ?>
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-username" class="control-label col-xs-12 col-sm-2"><?php echo __('Username'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-username" data-rule="required" class="form-control" name="row[username]" type="text" value="<?php echo htmlentities($row['username']); ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-nickname" class="control-label col-xs-12 col-sm-2"><?php echo __('Nickname'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-nickname" data-rule="required" class="form-control" name="row[nickname]" type="text" value="<?php echo htmlentities($row['nickname']); ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-password" class="control-label col-xs-12 col-sm-2"><?php echo __('Password'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-password" data-rule="password" class="form-control" name="row[password]" type="password" value="" placeholder="<?php echo __('Leave password blank if dont want to change'); ?>" autocomplete="new-password" />
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-email" class="control-label col-xs-12 col-sm-2"><?php echo __('Email'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-email" data-rule="" class="form-control" name="row[email]" type="text" value="<?php echo htmlentities($row['email']); ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-mobile" class="control-label col-xs-12 col-sm-2"><?php echo __('Mobile'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-mobile" data-rule="" class="form-control" name="row[mobile]" type="text" value="<?php echo htmlentities($row['mobile']); ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-avatar" class="control-label col-xs-12 col-sm-2"><?php echo __('Avatar'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-avatar" data-rule="" class="form-control" size="50" name="row[avatar]" type="text" value="<?php echo $row['avatar']; ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-avatar" class="btn btn-danger faupload" data-input-id="c-avatar" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-avatar"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-avatar" class="btn btn-primary fachoose" data-input-id="c-avatar" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-avatar"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-avatar"></ul>
        </div>
    </div>-->
    <div class="form-group">
        <label for="c-role_level" class="control-label col-xs-12 col-sm-2"><?php echo __('用户等级'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-role_level" data-rule="required" class="form-control" name="row[role_level]" type="number" value="<?php echo $row['role_level']; ?>">
        </div>
    </div>
    <!--<div class="form-group">
        <label for="c-gender" class="control-label col-xs-12 col-sm-2"><?php echo __('Gender'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[gender]', ['1'=>__('Male'), '0'=>__('Female')], $row['gender']); ?>
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-birthday" class="control-label col-xs-12 col-sm-2"><?php echo __('Birthday'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-birthday" data-rule="" class="form-control datetimepicker" data-date-format="YYYY-MM-DD" data-use-current="true" name="row[birthday]" type="text" value="<?php echo $row['birthday']; ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-bio" class="control-label col-xs-12 col-sm-2"><?php echo __('Bio'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-bio" data-rule="" class="form-control" name="row[bio]" type="text" value="<?php echo htmlentities($row['bio']); ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-token_value" class="control-label col-xs-12 col-sm-2"><?php echo __('用户余额'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-token_value" data-rule="required" class="form-control" name="row[token_value]" type="number" value="<?php echo $row['token_value']; ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-score" class="control-label col-xs-12 col-sm-2"><?php echo __('Score'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-score" data-rule="required" class="form-control" name="row[score]" type="number" value="<?php echo $row['score']; ?>">
        </div>
    </div>-->
    <!--<div class="form-group">
        <label for="c-successions" class="control-label col-xs-12 col-sm-2"><?php echo __('Successions'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-successions" data-rule="required" class="form-control" name="row[successions]" type="number" value="<?php echo $row['successions']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-maxsuccessions" class="control-label col-xs-12 col-sm-2"><?php echo __('Maxsuccessions'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-maxsuccessions" data-rule="required" class="form-control" name="row[maxsuccessions]" type="number" value="<?php echo $row['maxsuccessions']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-prevtime" class="control-label col-xs-12 col-sm-2"><?php echo __('Prevtime'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-prevtime" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[prevtime]" type="text" value="<?php echo datetime($row['prevtime']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-logintime" class="control-label col-xs-12 col-sm-2"><?php echo __('Logintime'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-logintime" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[logintime]" type="text" value="<?php echo datetime($row['logintime']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-loginip" class="control-label col-xs-12 col-sm-2"><?php echo __('Loginip'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-loginip" data-rule="required" class="form-control" name="row[loginip]" type="text" value="<?php echo $row['loginip']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-loginfailure" class="control-label col-xs-12 col-sm-2"><?php echo __('Loginfailure'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-loginfailure" data-rule="required" class="form-control" name="row[loginfailure]" type="number" value="<?php echo $row['loginfailure']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-joinip" class="control-label col-xs-12 col-sm-2"><?php echo __('Joinip'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-joinip" data-rule="required" class="form-control" name="row[joinip]" type="text" value="<?php echo $row['joinip']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="c-jointime" class="control-label col-xs-12 col-sm-2"><?php echo __('Jointime'); ?>:</label>
        <div class="col-xs-12 col-sm-4">
            <input id="c-jointime" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[jointime]" type="text" value="<?php echo datetime($row['jointime']); ?>">
        </div>
    </div>-->

    <div class="form-group">
        <label for="c-audit_remark" class="control-label col-xs-12 col-sm-2"><?php echo __('审核备注'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-audit_remark" data-rule="" class="form-control" name="row[audit_remark]" type="text" value="<?php echo $row['audit_remark']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="is_audit" class="control-label col-xs-12 col-sm-2"><?php echo __('审核'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[is_audit]', [1=>__('通过'), 2=>__('不通过')], $row['is_audit']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="is_valid" class="control-label col-xs-12 col-sm-2"><?php echo __('有效用户'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[is_valid]', [1=>__('是'), 0=>__('否')], $row['is_valid']); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="is_activated" class="control-label col-xs-12 col-sm-2"><?php echo __('激活用户'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[is_activated]', [1=>__('是'), 0=>__('否')], $row['is_activated']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="can_transfer" class="control-label col-xs-12 col-sm-2"><?php echo __('能否转账'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_transfer]', [1=>__('能'), 0=>__('否')], $row['can_transfer']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="can_extract" class="control-label col-xs-12 col-sm-2"><?php echo __('能否提现'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_extract]', [1=>__('能'), 0=>__('否')], $row['can_extract']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="can_get_rebates" class="control-label col-xs-12 col-sm-2"><?php echo __('能否获得返利'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_get_rebates]', [1=>__('能'), 0=>__('否')], $row['can_get_rebates']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="can_get_dividends" class="control-label col-xs-12 col-sm-2"><?php echo __('能否获得分红'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_get_dividends]', [1=>__('能'), 0=>__('否')], $row['can_get_dividends']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="can_login" class="control-label col-xs-12 col-sm-2"><?php echo __('能否登录'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[can_login]', [1=>__('能'), 0=>__('否')], $row['can_login']); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="content" class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[status]', ['normal'=>__('Normal'), 'hidden'=>__('Hidden')], $row['status']); ?>
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
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
