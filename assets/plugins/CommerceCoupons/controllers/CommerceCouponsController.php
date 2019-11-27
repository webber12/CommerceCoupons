<?php namespace CommerceCoupons;

class CommerceCouponsController
{
    private static $instance = null;

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
        if (!empty($_SESSION['CommerceCoupon'])) {
            $this->modx->db->insert([ 'order_id' => $params['order_id'], 'coupon_id' => $_SESSION['CommerceCoupon'] ], $this->table_orders);
        }
    }
    
    public function destroyCoupon()
    {
        if (!empty($_SESSION['CommerceCoupon'])) {
            unset($_SESSION['CommerceCoupon']);
        }
    }
    
    public function ckechCoupon($coupon)
    {
        $output = ['status' => 'error', 'message' => 'unactive'];
        $time = date("Y-m-d", time());
        $sql = "SELECT id,limit_orders FROM " . $this->table . " WHERE active=1 AND `code`='" . $coupon . "' AND (date_start IS NULL OR `date_start`<='" . $time . "') AND (date_finish IS NULL OR `date_finish`>='" . $time . "') LIMIT 0,1";
        $q = $this->modx->db->query($sql);
        if ($this->modx->db->getRecordCount($q) > 0) {
            $row = $this->modx->db->getRow($q);
            $orders = $this->modx->db->getValue("SELECT COUNT(*) FROM " . $this->table_orders . " WHERE coupon_id=" . $row['id']);
            if ($orders < $row['limit_orders']) {
                $output = ['status' => 'ok', 'message' => 'add'];
                $_SESSION['CommerceCoupon'] = $row['id'];
            } else {
                $output['message'] = 'limits';
            }
        }
        return $output;
    }
    
}