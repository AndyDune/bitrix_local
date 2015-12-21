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
        'load_catalog_import_file' => [
            'event' => 'catalog.import.file.load',
            'minute' => 5,
            'hour' => 5
        ],
        'export.order.to.the.x' => [
            'event' => 'export.order.to.the.x',
            'minute' => [6, 12, 18, 24, 30, 36, 42, 48, 54],
            'hour' => '' // Кажный час
        ],

    ]
]
*/
$params = [];
if (!isset($_SERVER['DOCUMENT_ROOT']) or !$_SERVER['DOCUMENT_ROOT']) {
    $dir =  __DIR__ . '/..'; //
    $_SERVER['DOCUMENT_ROOT'] = $dir;
    if (isset($_SERVER['argv'][1])) {
        $_REQUEST['event'] = $_SERVER['argv'][1];
    }
    if (isset($_SERVER['argv'][2]) and $_SERVER['argv'][2]) {
        parse_str($_SERVER['argv'][2], $params);
    }
} else {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

use Rzn\Library\Registry;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$sm = Registry::getServiceManager();

/** @var Rzn\Library\EventManager\EventManager $events */
$events = $sm->get('event_manager');

$config = $sm->get('config');
$cron = $config['cron'];

/** @var \Rzn\Library\Waterfall\WaterfallCollection $waterfall */
$waterfall = $sm->get('waterfall');

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

            if (isset($task['event'])) {
                $events->trigger($task['event'], null, $params);
                echo ' Отработоло событие: ', $task['event'];
            }
            if (isset($task['waterfall'])) {
                $waterfall->execute($task['waterfall'], $params);
                echo ' Был запущен водопад: ', $task['waterfall'];
            }
        }
        throw new Exception('Прыг');
    }

    foreach ($tasks as $name => $task) {
        if (isset($task['lock']) and $task['lock']) {
            continue;
        }
        if (isset($task['params']) and $task['params'] and is_array($task['params'])) {
            $params = $task['params'];
        } else {
            $params = [];
        }

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
        if (isset($task['event'])) {
            $events->trigger($task['event'], null, $params);
        }
        if (isset($task['waterfall'])) {
            $waterfall->execute($task['waterfall'], $params);
        }

        echo ' Отработоло событие: ', $task['event'];
        break;
    }
} catch (Exception $e) {

}
echo ' Крон отработал нормально ';

