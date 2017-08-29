<?php
/**
 * @author Duncan Chiang <duncan.c@inwinstack.com>
 * 
 * 
 * ownCloud - openidconnect
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 * 
 * @copyright Copyright (c) 2017, inwinSTACK, Inc.
 * @license AGPL-3.0
 */

namespace OCA\OpenIdConnect\AppInfo;

use OCP\AppFramework\App;

$app = new App('openidconnect');

$container = $app->getContainer();

$container->registerService("L10N", function($c) {
    return $c->getServerContainer()->getL10N("openidconnect");
});

$application = new Application();
$application->getContainer()->query('UserHooks')->register();

//\OCP\Util::connectHook('OC_User', 'post_deleteUser', '\OCA\OpenIdConnect\Util', 'deleteUser');
$request = \OC::$server->getRequest();

if( $request->offsetGet("access_token") && !$request->offsetGet("asus")) {
    $processor = new \OCA\OpenIdConnect\SingleSignOnProcessor();
    $processor->run();
}

\OCP\Util::addScript("openidconnect", "script");


