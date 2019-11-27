<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    define('MODX_API_MODE', true);
    include_once(realpath('../../../index.php'));
    if (empty($modx->config)) {
        $modx->getSettings();
    }
    $output = '';

    if (isset($_REQUEST['action'])) {
        $output = '';
        $action = $modx->db->escape($_REQUEST['action']);
        $filename = strtolower($action);
        $classname = ucfirst($action);
        if (!class_exists('\\CommerceCoupons\\Ajax\\' . $classname, false)) {
            if (file_exists(__DIR__ . '/ajax/' . $filename . '.php')) {
                require_once (__DIR__ . '/ajax/' . $filename . '.php');
                $controllerClass = '\\CommerceCoupons\\Ajax\\' . $classname;
                $controller = new $controllerClass($modx);
                $output = $controller->run();
            }
        }
        echo $output;
    }
exit;
}
exit;
