<?php
style("openidconnect", "styles");
?>
<ul>
    <li class='update'>
        <?php p($l->t('Logout success.')); ?><br/><br/>
        <a class="button" href="<?php echo \OC_Config::getValue("openid_login_url")?>"><?php p($l->t("Login again.")) ?></a>
    </li>
</ul>
