<?php
/**
 * ownCloud - openidconnect
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author dauba <dauba.k@inwinstack.com>
 * @copyright dauba 2015
 */

namespace OCA\OpenIdConnect\AppInfo;

use OCP\AppFramework\App;

$app = new App('openidconnect');
//$application = new Application();
$container = $app->getContainer();

$container->registerService("L10N", function($c) {
    return $c->getServerContainer()->getL10N("openidconnect");
});

$request = \OC::$server->getRequest();

if( $request->offsetGet("access_token") && !$request->offsetGet("asus")) {
    $processor = new \OCA\OpenIdConnect\SingleSignOnProcessor();
    $processor->run();
}

\OCP\Util::addScript("openidconnect", "script");
