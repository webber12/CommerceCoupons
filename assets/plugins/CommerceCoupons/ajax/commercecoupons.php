<?namespace CommerceCoupons\Ajax;

include_once ("actions.php");

use \CommerceCoupons\CommerceCouponsController;

class Commercecoupons extends \CommerceCoupons\Ajax\Actions
{
    public function run()
    {
        $output = ['status' => 'error', 'message' => 'empty'];
        if (!empty($_POST['coupon'])) {
            $coupon = trim($this->modx->db->escape($_POST['coupon']));
            $output = CommerceCouponsController::getInstance()->checkCoupon($coupon);
        }
        if ($output['status'] != 'ok' && !empty($_SESSION['CommerceCoupon'])) unset($_SESSION['CommerceCoupon']);
        return json_encode($output);
    }

}