<?php

$parts = parse_url($_SERVER['QUERY_STRING']);
parse_str($parts['path'], $query);
if (!isset($query['code'])) {
   header("Location: https://oidc.tanet.edu.tw/oidc/v1/azp?response_type=code&client_id=9f0dba9a636c42a3a074128804675556&redirect_uri=https://moe1.owncloud.com/openid.php&scope=openid+email+profile+edudata&state=mdu09QmEXXYhEQpVrIiI2sNUqbIXgqphPqzpRgOArww&nonce=_pFWU8VbN43yfGlOGgutRZODR6iCp_10LN8aa4IMy-s"); 
}

else {
    $data = 'grant_type=authorization_code&code=' . $query['code'] . '&redirect_uri=https://moe1.owncloud.com/openid.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://oidc.tanet.edu.tw/oidc/v1/token');
    curl_setopt($ch, CURLOPT_POST, true); 
    //curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERPWD, "9f0dba9a636c42a3a074128804675556:85f30e32169afed8e837170f852e07f1e6cb3ac36b0efb802cf92d6a1cdbb5d6");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/x-www-form-urlencoded'
    ));
    $result = curl_exec($ch);
    
    $pageError = "<html><head><title>Error</title></head><body>Internal Server Error</body></html>";
    $limit = 0;
    while((curl_errno($ch) == 28 || $result == $pageError ) && $limit < 4){
        $limit++;
        $result = curl_exec($ch);
    }

    $result = json_decode($result, true);

    $ip = $_SERVER["REMOTE_ADDR"];

    $params['asus'] = false;

    if($result['access_token'] != null) {
        $params['access_token'] = $result['access_token'];
        $params["userip"] = $ip;
        curl_close($ch);

        header('Location: https://moe1.owncloud.com/?'. http_build_query($params));
        exit();
    }
    else {
        $msg = "無法取得 OpenID connect 合法授權資訊";
    }
}

?>

<html>
<head>
    <meta charset="UTF-8">
    <title>
        雲端儲存服務        </title>
    <link rel="shortcut icon" type="image/png" href="core/img/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="core/img/favicon-touch.png">
    <link rel="stylesheet" href="core/css/styles.css" media="screen">
    <link rel="stylesheet" href="core/css/header.css" media="screen">
    <link rel="stylesheet" href="core/css/mobile.css" media="screen">
    <link rel="stylesheet" href="core/css/icons.css" media="screen">
    <link rel="stylesheet" href="core/css/fonts.css" media="screen">
    
    <link rel="stylesheet" href="core/css/apps.css" media="screen">
    <link rel="stylesheet" href="core/css/fixes.css" media="screen">
    <link rel="stylesheet" href="core/css/multiselect.css" media="screen">
    <link rel="stylesheet" href="core/vendor/jquery-ui/themes/base/jquery-ui.css" media="screen">
    <link rel="stylesheet" href="core/css/jquery-ui-fixes.css" media="screen">
    <link rel="stylesheet" href="core/css/tooltip.css" media="screen">
    <link rel="stylesheet" href="core/css/share.css" media="screen">
    <link rel="stylesheet" href="core/css/jquery.ocdialog.css" media="screen">
    
    <link rel="stylesheet" href="themes/MOE/core/css/styles.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/header.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/icons.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/apps.css" media="screen">
    <!--
    <link rel="stylesheet" href="login/styles/vendor/bootstrap.css" />
    <link rel="stylesheet" href="login/styles/vendor/font-awesome.css" />
    -->
</head>
<body id="body-login">
    <div class="wrapper">
        <div class="v-align">
            <header role="banner">
                <div id="header">
                    <div class="logo svg">
                        <h1 class="hidden-visually">
                            雲端儲存服務                                </h1>
                    </div>
                    <div id="logo-claim" style="display:none;"></div>
                </div>
            </header>
                                
            <form method="post" name="login">
                <fieldset>
                    <div id="message" class="hidden">
                        <img class="float-spinner" alt="" src="/core/img/loading-dark.gif">
                        <span id="messageText"></span>
                        <div style="clear: both;"></div>
                    </div>
                    <!--<p class="grouptop">
                        <input type="text" name="account" id="user" placeholder="使用者名稱" value="" autofocus="" autocomplete="on" autocapitalize="off" autocorrect="off" required="">
                        <label for="user" class="infield">使用者名稱</label>
                    </p>

                    <p class="groupbottom">
                        <input type="password" name="password" id="password" value="" placeholder="密碼" autocomplete="on" autocapitalize="off" autocorrect="off" required="">
                        <label for="password" class="infield">密碼</label>
                        <input type="submit" id="submit" class="login primary icon-confirm svg" title="登入" value="">
                    </p>

                    <div class="remember-login-container">
                        <input type="checkbox" name="remember_login" value="1" id="remember_login" class="checkbox checkbox--white">
                        <label for="remember_login">remember</label>
                    </div>-->
                    <input type="hidden" name="timezone-offset" id="timezone-offset" value="8">
                    <input type="hidden" name="timezone" id="timezone" value="Asia/Shanghai">
                    <input type="hidden" name="requesttoken" value="aUsFDmwGEgcCbkMqFScfJC05JUgIfitlCkEGKDNg:02ITZ5TJC89dBkhrCts0b2q1fxLZY0">
                </fieldset>
            </form>
            <div class="push">
            <?php if(isset($msg)) echo "<p style='color:red;'>$msg</p>"; ?>
	    </div>
        </div>
    </div>
    <footer role="contentinfo">
        <div class="footer-img"></div>
        <!--<div style="display: inline-block">
            請使用教育體系 OpenID 帳號進行登入<br>
            Copyright © Ministry of Education. All rigths reserved.
        </div>-->
    </footer>
</body>
</html>