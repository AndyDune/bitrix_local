<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$sm = Rzn\Library\Registry::getServiceManager();
/** @var Rzn\Library\Component\IncludeWithTemplate $includeWithTemplate */
$includeWithTemplate = $sm->get('IncludeComponentWithTemplate');
$includeWithTemplate->includeComponent('static/index');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");