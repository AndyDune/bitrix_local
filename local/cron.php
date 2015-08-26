<?php
/**
 * ----------------------------------------------------
 * | Автор: Андрей Рыжов (Dune) <info@rznw.ru>         |
 * | Сайт: www.rznw.ru                                 |
 * | Телефон: +7 (4912) 51-10-23                       |
 * | Дата: 21.07.2015                                      
 * ----------------------------------------------------
 *
 * Скрипт для фундаментально запуска регуляртных иснтрукций.
 * Испльзует описание из конфига вида:
 *
 * event -
 * minute -
 * hour -
 *
'cron' => [
    'tasks' => [
        'import_catalog_from_file' => [
            'event' => 'import.catalog.to.start',
            'minute' => 5,
            'hour' => 8
        ],
    ]
]
*/

if (!isset($_SERVER['DOCUMENT_ROOT']) or !$_SERVER['DOCUMENT_ROOT']) {
    $dir =  __DIR__ . '/..'; //
    $_SERVER['DOCUMENT_ROOT'] = $dir;
    if (isset($_SERVER['argv'][1])) {
        $_REQUEST['event'] = $_SERVER['argv'][1];
    }
}

use Rzn\Library\Registry;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$sm = Registry::getServiceManager();

/** @var Rzn\Library\EventManager\EventManager $events */
$events = $sm->get('event_manager');

$config = $sm->get('config');
$cron = $config['cron'];

$minute = str_replace('0', '', date('i'));
$hour = date('G');

if (!isset($cron['tasks'])) {
    return null;
}
$tasks = $cron['tasks']->toArray();
try {

    if ($cron['direct'] and isset($_REQUEST['event'])) {
        if (isset($tasks[$_REQUEST['event']])) {
            $task = $tasks[$_REQUEST['event']];
            $events->trigger($task['event']);
            echo ' Отработоло событие: ', $task['event'];
        }
        throw new Exception('Прыг');
    }

    foreach ($tasks as $name => $task) {
        if ($task['hour'] and !is_array($task['hour'])) {
            $task['hour'] = [$task['hour']];
        }
        if ($task['hour'] and is_array($task['hour']) and !in_array($hour, $task['hour'])) {
            continue;
        }

        if (!isset($task['minute']) or !$task['minute']) {
            continue;
        }

        if (!is_array($task['minute'])) {
            $task['minute'] = [$task['minute']];
        }

        if (!in_array($minute, $task['minute'])) {
            continue;
        }
        // Запуск события
        $events->trigger($task['event']);
        echo ' Отработоло событие: ', $task['event'];
        break;
    }
} catch (Exception $e) {

}
echo ' Крон отработал нормально ';

