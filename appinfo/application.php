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

use OCA\OpenIdConnect\UserHooks;
class Application extends App {
    /**
     * Define your dependencies in here
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('openidconnect', $urlParams);

        $container = $this->getContainer();

        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserSession(),
                $c->query('ServerContainer')->getRootFolder()
            );
        });
    }
}

