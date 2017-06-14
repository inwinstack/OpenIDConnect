<?php

namespace OCA\OpenIdConnect;

interface ISingleSignOnRequest {
    const VALIDTOKEN = "validtoken";
    const INFO = "info";
    const USERPASSWORDGENERATOR = "userpasswordgenerator";
    const USERINFO = "userinfo";
    const USERPROFILE = "getinfo";
    const INVALIDTOKEN = "invalidtoken";
    const GETTOKEN = "gettoken";

    public function name();
    public function send($data);
    public function getErrorMsg();
}

