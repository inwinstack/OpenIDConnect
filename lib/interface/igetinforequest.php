<?php
namespace OCA\OpenIdConnect;
include ('isinglesignonrequest.php');


interface IGetInfoRequest extends ISingleSignOnRequest {

    public function setup($params);

    public function getSchoolId();

    public function getGroups();

    public function getDisplayName();

    public function hasPermission();

    public function hasErrorMsg();
}
