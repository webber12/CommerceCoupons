<? namespace CommerceCoupons\Ajax;

include_once(__DIR__ . '/../controllers/CommerceCouponsController.php');

use \CommerceCoupons\CommerceCouponsController;

class Actions
{

    public function __construct($modx, $params = array())
    {
        $this->modx = $modx;
        $this->params = $params;
    }

}