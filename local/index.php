<?php
 /**
  * ----------------------------------------------------
  * | Автор: Андрей Рыжов (Dune) <info@rznw.ru>         |
  * | Сайт: www.rznw.ru                                 |
  * | Телефон: +7 (4912) 51-10-23                       |
  * | Дата: 03.12.14                                      
  * ----------------------------------------------------
  *
  */
use Rzn\Library\Registry;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

try {
    $sm = Registry::getServiceManager();
    /**
     * @param Rzn\Library\Request $request
     */
    $request = $sm->get('request');
    $isAjax = $request->isAjax();
    if (!$isAjax) {
        throw new Exception('Пока ждем только аякс.');
    }

    if (!isset($_REQUEST['component']) or !$_REQUEST['component']) {
        throw new Exception('Ждем название компонента.');
    }

    $params = ['component' => $_REQUEST['component'], 'ajax' => $isAjax];
    $eventManager = $sm->get('event_manager');
    $params = $eventManager->prepareArgs($params);

    $result = $eventManager->trigger('direct.include.component.before', null, $params);
    if ($result->stopped()) {
        throw new Exception($results->last());
    }

    /**
     * Подключение файла с компонентом в рамках текущего шаблона
     * @param Rzn\Library\Component\IncludeWithTemplate $includeWithTemplate
     */

    /** @var Rzn\Library\Component\IncludeWithTemplate  $includeWithTemplate */
    $includeWithTemplate = $sm->get('IncludeComponentWithTemplate');
    $includeWithTemplate->setFreeInclude();
    $includeWithTemplate->includeComponent($params['component']); // имеет метод __invoke

    if (!$includeWithTemplate->isSuccess()) {
        throw new Exception('Компонент с таким кодовым именем не зарегистрирован.');
    }

    $eventManager->trigger('direct.include.component.success', null, $params);
    /*
        $path = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/' . SITE_TEMPLATE_ID . '/include/component/' . $_REQUEST['component'] . '.php';

        if (!is_file($path)) {
            throw new Exception('Компонент с таким кодовым именем не зарегистрирован.');
        }

        include($path);
    */

}  catch (Exception $e) {
    //echo SITE_TEMPLATE_ID;
    $APP = Registry::getApplication();

    CHTTP::SetStatus("404 Not Found");
    @define("ERROR_404","Y");

    $APP->SetTitle("Страница не найдена");
    include(__DIR__ . '/templates/' . SITE_TEMPLATE_ID . '/404.php');

}

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");