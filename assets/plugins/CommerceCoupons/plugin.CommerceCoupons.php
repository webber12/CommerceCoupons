<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$e = $modx->event;
$events = ['OnWebPageInit', 'OnCollectSubtotals', 'OnOrderSaved'];
if (in_array($e->name, $events)) {
    include_once MODX_BASE_PATH . "assets/plugins/CommerceCoupons/controllers/CommerceCouponsController.php";
    $controller = \CommerceCoupons\CommerceCouponsController::getInstance($params);
}

switch ($e->name) {
    case 'OnWebPageInit':
        $controller->regScripts();
        break;
        
    case 'OnCollectSubtotals': 
        $discount = $controller->getDiscount();
        if (!empty($discount)) {
            $summ = 0;
            switch ($discount['type']) {
                case 'percent':
                    $summ = $params['total'] * $discount['amount'] / 100;
                    break;
                case 'summ':
                    $summ = ci()->currency->convertToActive($discount['amount']);
                    break;
                break;
            }
            $params['total'] -= $summ;
            $params['rows']['CommerceCoupons'] = [
                'title' => 'Скидка по купону <b>' . $discount['code'] . '</b>',
                'price' => -$summ,
            ];
        }
        break;
        
    case 'OnOrderSaved':
        $controller->rememberOrder($params);
        $discount = $controller->destroyCoupon();
        break;
        
    default:
        break;
}