<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$e = $modx->event;
$events = ['OnWebPageInit', 'OnCollectSubtotals', 'OnOrderSaved', 'OnBeforeCartItemAdding', 'OnManagerBeforeOrderRender'];
if (in_array($e->name, $events)) {
    include_once MODX_BASE_PATH . "assets/plugins/CommerceCoupons/controllers/CommerceCouponsController.php";
    $controller = \CommerceCoupons\CommerceCouponsController::getInstance($params);
}

switch ($e->name) {
    case 'OnWebPageInit':
        $controller->regScripts();
        break;
        
    case 'OnCollectSubtotals': 
        $params = $controller->OnCollectSubtotals($params);
        break;

    case 'OnBeforeCartItemAdding': 
        $params = $controller->OnBeforeCartItemAdding($params);
        break;
        
    case 'OnOrderSaved':
        $controller->rememberOrder($params);
        $discount = $controller->destroyCoupon();
        break;

    case 'OnManagerBeforeOrderRender':
        $controller->OnManagerBeforeOrderRender($params);
        break;

    default:
        break;
}