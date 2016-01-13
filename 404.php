<?

// Обработка собственных редиректов
include($_SERVER['DOCUMENT_ROOT'] . '/local/urlrewrite.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$sm = Rzn\Library\Registry::getServiceManager();
/** @var Rzn\Library\Component\IncludeWithTemplate $includeWithTemplate */
$includeWithTemplate = $sm->get('IncludeComponentWithTemplate');
$includeWithTemplate->includeComponent('404');


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");