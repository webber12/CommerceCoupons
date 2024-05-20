<?php namespace CommerceCoupons;

class CommerceCouponsController
{
    private static $instance = null;
    protected $modx;
    protected $params;
    protected $table;
    protected $table_orders;

    public function __construct($params = [])
    {
        $this->modx = EvolutionCMS();
        $this->params = $params;
        $this->table = $this->modx->getFullTableName("commerce_coupons");
        $this->table_orders = $this->modx->getFullTableName("commerce_coupons_orders");
    }

    public static function getInstance($params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($params);
        }
        return self::$instance;
    }

    public function regScripts()
    {
        if (empty($this->params['docs']) || in_array($this->modx->documentIdentifier, array_map('trim', explode(',', $this->params['docs'])))) {
            $this->modx->regClientScript(MODX_SITE_URL . "assets/plugins/CommerceCoupons/js/CommerceCoupons.js");
        }
    }
    
    public function getDiscount()
    {
        $discount = array();
        return $discount;
        if (!empty($_SESSION['CommerceCoupon'])) {
            $q = $this->modx->db->query("SELECT * FROM " . $this->table . " WHERE id=" . $_SESSION['CommerceCoupon']);
            if ($this->modx->db->getRecordCount($q) == 1) {
                $row = $this->modx->db->getRow($q);
                if (!empty($row['discount']) && $row['discount'] != '0.00') {
                    $discount = array_merge($row, ['amount' => $row['discount'], 'type' => 'percent']);
                } else {
                    $discount = array_merge($row, ['amount' => $row['discount_summ'], 'type' => 'summ']);
                }
            }
        }
        return $discount;
    }
    
    public function rememberOrder($params)
    {
        $couponInfo = $this->getCouponInfo();
        if (!empty($couponInfo)) {
            $this->modx->db->insert(
                [ 'order_id' => $params['order_id'], 'coupon_id' => $couponInfo['id'], 'coupon_info' => json_encode($couponInfo, JSON_UNESCAPED_UNICODE) ],
                $this->table_orders
            );
        }
    }
    
    public function destroyCoupon()
    {
        unset($_SESSION['CommerceCoupon']);
    }
    
    public function checkCoupon($coupon)
    {
        $output = ['status' => 'error', 'message' => 'unactive'];
        $time = date("Y-m-d", time());
        $sql = "SELECT * FROM " . $this->table . " WHERE active=1 AND BINARY `code`='" . $coupon . "' AND (date_start IS NULL OR `date_start`<='" . $time . "') AND (date_finish IS NULL OR `date_finish`>='" . $time . "') ORDER BY id desc LIMIT 0,1";
        $q = $this->modx->db->query($sql);
        if ($this->modx->db->getRecordCount($q) > 0) {
            $row = $this->modx->db->getRow($q);
            $orders = $this->modx->db->getValue("SELECT COUNT(*) FROM " . $this->table_orders . " WHERE coupon_id=" . $row['id']);
            if (empty($row['limit_orders']) || $orders < $row['limit_orders']) {
                $output = ['status' => 'ok', 'message' => 'add'];
                $couponInfo = $this->makeCouponInfo($row);
                // сохраняем информацию о применяемом купоне,
                // чтобы затем использовать его в плагине для модификации цены и общей стоимости корзины
                $this->setCouponInfo($couponInfo);
                // необходимо перегрузить все товары в корзине, чтобы
                // избавиться от действия предыдущего купона и применить новый
                $this->reloadCart();
            } else {
                $output['message'] = 'limits';
            }
        }
        return $output;
    }

    public function OnCollectSubtotals($params)
    {
        $couponInfo = $this->getCouponInfo();
        if(!empty($couponInfo['info']['type']) && $couponInfo['info']['type'] == 'cart') {
            //купон необходимо применить ко всей корзине
            $cart = ci()->carts->getCart($this->getActiveCart());
            $total = $cart->getTotal();
            $min = ci()->currency->convertToActive($couponInfo['info']['minsumm'] ?? 0);
            if($total >= $min) {
                $summ = 0;
                switch ($couponInfo['type']) {
                    case 'percent':
                        $summ = $params['total'] * $couponInfo['amount'] / 100;
                        break;
                    case 'summ':
                        $summ = ci()->currency->convertToActive($couponInfo['amount']);
                        break;
                }
                if(!empty($summ)) {
                    $params['total'] -= $summ;
                    $params['rows']['CommerceCoupons'] = [
                        'title' => $this->getTranslation('coupon.discount', 'Cкидка по купону') .  ' <b>' . $couponInfo['code'] . '</b>:',
                        'price' => -$summ,
                    ];
                }
            }
        }
        return $params;
    }

    public function OnBeforeCartItemAdding($params)
    {
        $couponInfo = $this->getCouponInfo();
        if(!empty($couponInfo['info']['type']) && $couponInfo['info']['type'] == 'product') {
            //купон применяется к конкретному товару
            //проверяем, что он применяется к данному товару
            if(empty($couponInfo['info']['ids']) || (!empty($couponInfo['info']['ids']) && in_array($params['item']['id'], $couponInfo['info']['ids']))) {
                $original_price = !empty($params['item']['meta']['original_price']) ? $params['item']['meta']['original_price'] : $params['item']['price'];
                $new_price = 0;
                switch ($couponInfo['type']) {
                    case 'percent':
                        $new_price = $original_price - ($original_price * $couponInfo['amount'] / 100);
                        break;
                    case 'summ':
                        $new_price = ($original_price - $couponInfo['amount'] >= 0) ? $original_price - $couponInfo['amount'] : 0;
                        break;
                    default:
                        break;
                }
                $params['item']['price'] = $new_price;
                $params['item']['meta']['CommerceCoupon'] = $couponInfo;
            } else {
                unset($params['item']['meta']['CommerceCoupon']);
            }

        }
        return $params;
    }

    public function OnManagerBeforeOrderRender($params)
    {
        $sql = "select * from " . $this->table_orders . " where order_id=" . $params['order']['id'] . " limit 1";
        $q = $this->modx->db->query($sql);
        if($this->modx->db->getRecordCount($q) == 1) {
            $row = $this->modx->db->getRow($q);
            if(!empty($row['coupon_info'])) {
                $couponInfo = json_decode($row['coupon_info'], 1);
                $params['groups']['order_info']['fields']['coupon'] = [
                    'title' => '<b>Купон</b>',
                    'content' => function($data) use($couponInfo) {
                        return $couponInfo['code'] . ' (#' . $couponInfo['id'] . ')';
                    },
                    'sort' => 55,
                ];
                $params['groups']['order_info']['fields']['coupon_type'] = [
                    'title' => 'Тип купона',
                    'content' => function($data) use($couponInfo) {
                        $out = '';
                        switch($couponInfo['info']['type']) {
                            case 'product':
                                $out = 'К продукту';
                                break;
                            case 'cart':
                                $out = 'К корзине';
                                break;
                            default:
                                break;
                        }
                        return $out;
                    },
                    'sort' => 56,
                ];
                $params['groups']['order_info']['fields']['coupon_discount'] = [
                    'title' => 'Скидка по купону',
                    'content' => function($data) use($couponInfo) {
                        $out = '';
                        switch($couponInfo['type']) {
                            case 'percent':
                                $out = $couponInfo['amount'] . '%';
                                break;
                            case 'summ':
                                $out = 'фикс сумма ' . $couponInfo['amount'];
                                break;
                            default:
                                break;
                        }
                        return $out;
                    },
                    'sort' => 57,
                ];
            }
        }
    }

    protected function setCouponInfo($couponInfo)
    {
        $_SESSION['CommerceCoupon'] = $couponInfo;
        return $this;
    }

    protected function getCouponInfo()
    {
        return $_SESSION['CommerceCoupon'] ?? false;
    }

    protected function makeCouponInfo($row)
    {
        $arr = $row;
        switch(true) {
            case (strpos($row['coupon_type'], 'cart') !== false) :
                //купон для всей корзины
                $tmp = array_map('trim', explode(':', $row['coupon_type'], 2));
                if(!empty($tmp[1]) && is_numeric($tmp[1])) {
                    $minsumm = $tmp[1];
                } else {
                    $minsumm = 0;
                }
                $arr['info'] = ['type' => 'cart', 'minsumm' => $minsumm ];
                break;
            case !empty($row['coupon_type']):
                $ids = $this->getProductIds($row['coupon_type']);
                $arr['info'] = ['type' => 'product', 'ids' => $ids ];
                break;
            default:
                $arr['info'] = ['type' => 'product', 'ids' => false ];
                break;
        }
        if (!empty($arr['discount']) && $arr['discount'] != '0.00') {
            $arr = array_merge($arr, ['amount' => $arr['discount'], 'type' => 'percent']);
        } else {
            $arr = array_merge($arr, ['amount' => $arr['discount_summ'], 'type' => 'summ']);
        }
        return $arr;
    }

    protected function getProductIds($str)
    {
        $tmp = array_map('trim', explode(';', $str));
        $ids = [];
        foreach($tmp as $row) {
            switch(true) {
                case (strpos($row, 'parents:') !== false):
                    //тут отбор id по родителю
                    $parents = explode(':', $row,2)[1];
                    $ids = $this->getIdsFromParents($ids, $parents);
                    break;
                    case (strpos($row, ':') !== false):
                        //тут отбор id по имени тв
                        $tv = explode(':', $row,2);
                        $ids = $this->getIdsFromTV($ids, $tv);
                        break;
                default:
                    $ids[] = $row;
            }
        }
        return array_unique($ids);
    }

    protected function getIdsFromParents($ids = [], $parents)
    {
        $q = $this->modx->db->query('select id from ' . $this->modx->getFullTableName('site_content') . ' where parent IN (' . $parents . ') and deleted=0 and published=1');
        while($row = $this->modx->db->getRow($q)) {
            $ids[] = $row['id'];
        }
        return $ids;
    }

    protected function getIdsFromTV($ids = [], $tv)
    {
        $sql = "select `v`.`contentid` from " . $this->modx->getFullTableName('site_tmplvar_contentvalues') . " v left join " . $this->modx->getFullTableName('site_tmplvars') . " t on t.id=v.tmplvarid where `t`.`name`='" . $tv[0] . "' and `v`.`value` IN('" . str_replace(",", "','", $tv[1]) . "')";
        $q = $this->modx->db->query($sql);
        while($row = $this->modx->db->getRow($q)) {
            $ids[] = $row['contentid'];
        }
        return $ids;
    }

    protected function reloadCart()
    {
        $couponInfo = $this->getCouponInfo();
        $this->modx->invokeEvent('OnWebPageInit', ['id' => $this->modx->getConfig('site_start')]);
        $cart = ci()->carts->getCart($this->getActiveCart());
        $items = $cart->getItems();
        $add = [];
        foreach($items as $item) {
            unset($item['meta']['CommerceCoupons']);
            $add[] = [
                'id' => $item['id'],
                'count' => $item['count'],
                'name' => $item['name'],
                'options' => $item['options'],
                'meta' => $item['meta'],
            ];
        }
        $cart->clean();
        $cart->addMultiple($add);
    }

    protected function getActiveCart()
    {
        $lang = $this->getActiveLang();
        return 'products' . (!empty($lang) ? '_' . $lang : '');
    }

    protected function getActiveLang()
    {
        return $_SESSION['evoBabel_curLang'] ?? false;
    }

    protected function getTranslation($key, $default)
    {
        return $_SESSION['perevod'][$key] ?? $default;
    }
    
}