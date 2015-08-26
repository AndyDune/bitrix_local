<?
use Rzn\Library\Registry;
use Bitrix\Main\Loader;


function pr($value, $is_die = false)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    if ($is_die)
        die();
}


if (Loader::includeModule('rzn.library'))
{
    $sm = Registry::getServiceManager();

    Registry::set('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'], true);

    $parts = explode('.', $_SERVER['HTTP_HOST']);
    $countParts = count($parts);

    if ($countParts > 1)
    {
        Registry::set('HTTP_HOST_BASE', $parts[$countParts - 2] . '.' . $parts[$countParts - 1]);
    }
    else {
        $config = $sm->get('config');
        Registry::set('HTTP_HOST_BASE', $config['main']['domain']);
    }

}

