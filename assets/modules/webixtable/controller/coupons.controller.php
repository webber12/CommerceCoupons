<?php namespace WebixTable;

include_once ("main.controller.php");

class CouponsController extends \WebixTable\MainController
{

    protected $inline_fields_width_default = 100;

    protected $inline_fields_width = ['id' => 80, 'name' => 200, 'code' => 100, 'date_start' => 85, 'date_finish' => 85, 'date_create' => 85, 'discount_summ' => 120];
    
    protected $checkbox_fields = ['active'];

    public function ajaxGenerateCoupons()
    {
        $defaults = [
            'length' => 8,
            'count' => 1,
            'limit' => 1,
            'active' => 0
        ];
        $params = array_merge($defaults, $_REQUEST);
        $params = $this->modx->db->escape($params);
        $coupons = $this->makeCoupons($params);
        unset($params['action'], $params['module_id'], $params['count'], $params['length'], $params['stay']);
        if (!empty($params['discount'])) unset($params['discount_summ']);
        $ins = [];
        foreach ($coupons as $coupon) {
            $ins[] = "'" . implode("','", [
                $params['name'],
                $coupon,
                $params['date_start'],
                $params['date_finish'],
                $params['discount'],
                $params['discount_summ'],
                $params['limit'],
                $params['active']
            ]) . "'";
        }
        $sql = "INSERT INTO " . $this->getTable() . " (`name`, `code`, `date_start`, `date_finish`, `discount`, `discount_summ`, `limit_orders`, `active`) VALUES " . "(" . implode("), (", $ins). ")";
        $ins = $this->modx->db->query($sql);
        $resp = 'error';
        if ($ins) {
            $resp = 'ok';
        }
        return $resp;
    }
    
    protected function makeCoupons($params)
    {
        $coupons = array();
        include_once $this->module_folder . 'lib/class.coupon.php';
        $generator = new \coupon;
        $coupons = $generator::generate_coupons($params['count'], ['length' => $params['length'], 'letters' => true, 'numbers' => true, 'mixed_case' => true]);
        return $coupons;
    }

    protected function invokeOnAfterRenderColumns($data)
    {
        $tmp = $data;
        foreach ($data as $k => $v) {
            if (in_array($v['id'], $this->checkbox_fields)) {
                $tmp[$k]['editor'] = "checkbox";
                $tmp[$k]['template'] = "{common.checkbox()}";
            }
        }
        $data = $tmp;
        return $data;
    }

    protected function invokeOnBeforeUpdateInline($data)
    {
        if (empty($data['date_start'])) $data['date_start'] = NULL;
        if (empty($data['date_finish'])) $data['date_finish'] = NULL;
        return $data;
    }

}

