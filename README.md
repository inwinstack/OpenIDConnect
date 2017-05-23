# Openid Connect 0.1
Place this app in **owncloud/apps/**

#Usage
Use this app to connect with OpenID connect provider by auth code flow

## Publish to App Store

First get an account for the [App Store](http://apps.owncloud.com/) then run:

    make appstore_package

The archive is located in build/artifacts/appstore and can then be uploaded to the App Store.

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:

    phpunit -c phpunit.xml

## Notice
You need to set this config value in *config.php* before enable this app:

- "openid_login_url" - where login page is
- "openid_requests" - what API method shoulb be invoked
- "openid_userinfo_url" - where the openid provicer uri is
- "sso_global_logout1" - where is the default logout page
- "sso_multiple_region" - the same as SingleSign app
- "sso_one_time_password1" - the same as SingleSign app