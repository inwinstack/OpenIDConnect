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