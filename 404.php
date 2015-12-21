<?
use Rzn\Library\Registry;

// Обработка собственных редиректов
include($_SERVER['DOCUMENT_ROOT'] . '/local/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

include(Registry::get('TEMPLATE_ROOT') . '/404.php');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>