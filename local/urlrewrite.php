<?
$bSkipRewriteChecking = false;

if (!defined("AUTH_404"))
    define("AUTH_404", "Y");


if (!defined("BX_URLREWRITE")) {
    define("BX_URLREWRITE", true);
}

$foundQMark = strpos($_SERVER["REQUEST_URI"], "?");
$requestUriWithoutParams = ($foundQMark !== false? substr($_SERVER["REQUEST_URI"], 0, $foundQMark) : $_SERVER["REQUEST_URI"]);
$requestParams = ($foundQMark !== false? substr($_SERVER["REQUEST_URI"], $foundQMark) : "");

//decode only filename, not parameters
$requestPage = urldecode($requestUriWithoutParams);

if(!defined("BX_UTF") && CUtil::DetectUTF8($_SERVER["REQUEST_URI"]))
{
    $requestPage = CharsetConverter::ConvertCharset($requestPage, "utf-8", (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "windows-1251"));
}

$requestUri = $requestPage.$requestParams;

$io = CBXVirtualIo::GetInstance();

$arUrlRewrite = array();
if(file_exists(__DIR__ . "/urlrewrite_terms.php")) {
    $arUrlRewrite = include(__DIR__ . "/urlrewrite_terms.php");
}

if (!CHTTP::isPathTraversalUri($_SERVER["REQUEST_URI"]))
{
    foreach($arUrlRewrite as $val)
    {
        if(preg_match($val["CONDITION"], $requestUri))
        {
            if (strlen($val["RULE"]) > 0)
                $url = preg_replace($val["CONDITION"], (strlen($val["PATH"]) > 0 ? $val["PATH"]."?" : "").$val["RULE"], $requestUri);
            else
                $url = $val["PATH"];

            if(($pos=strpos($url, "?"))!==false)
            {
                $params = substr($url, $pos+1);
                parse_str($params, $vars);
                unset($vars["SEF_APPLICATION_CUR_PAGE_URL"]);

                $_GET += $vars;
                $_REQUEST += $vars;
                $_SERVER["QUERY_STRING"] = $QUERY_STRING = CHTTP::urnEncode($params);
                $url = substr($url, 0, $pos);
            }

            $url = _normalizePath($url);

            if(!$io->FileExists($_SERVER['DOCUMENT_ROOT'].$url))
                continue;

            if (!$io->ValidatePathString($url))
                continue;

            $urlTmp = strtolower(ltrim($url, "/\\"));
            $urlTmp = str_replace(".", "", $urlTmp);
            $urlTmp7 = substr($urlTmp, 0, 7);

            if (($urlTmp7 == "upload/" || ($urlTmp7 == "bitrix/" && substr($urlTmp, 0, 16) != "bitrix/services/" && substr($urlTmp, 0, 18) != "bitrix/groupdavphp")))
                continue;

            $ext = strtolower(GetFileExtension($url));
            if ($ext != "php")
                continue;

            CHTTP::SetStatus("200 OK");

            $_SERVER["REAL_FILE_PATH"] = $url;
            include_once($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'].$url));
            die();
        }
    }
}