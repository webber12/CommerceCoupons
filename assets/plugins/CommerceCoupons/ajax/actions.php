<? namespace CommerceCoupons\Ajax;

include_once(__DIR__ . '/../controllers/CommerceCouponsController.php');

use \CommerceCoupons\CommerceCouponsController;

class Actions
{

    protected $modx;
    protected $params;

    public function __construct($modx, $params = array())
    {
        $this->modx = $modx;
        $this->params = $params;
    }

}