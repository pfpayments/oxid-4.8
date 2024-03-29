<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

require_once(OX_BASE_PATH . "modules/pfc/PostFinanceCheckout/autoload.php");



use Monolog\Logger;
use Pfc\PostFinanceCheckout\Application\Model\Alert;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class NavigationController.
 * Extends \navigation.
 *
 * @mixin \oxadminview
 */
class pfcpostfinancecheckout_navigation extends pfcpostfinancecheckout_navigation_parent
{
	public function render() {
		$result = parent::render();
		if($result === 'header.tpl') {
			return 'pfcPostFinanceCheckoutHeader.tpl';
		}
		return $result;
	}
	
	public function getPfcAlerts()
    {
        $alerts = array();
        foreach (Alert::loadAll() as $row) {
            if ($row[1] > 0) {
                switch ($row[0]) {
                    case Alert::KEY_MANUAL_TASK:
                        $alerts[] = array(
                            'func' => $row[2],
                            'target' => $row[3],
                            'title' => PostFinanceCheckoutModule::instance()->translate("Manual Tasks (!count)", true, array('!count' => $row[1]))
                        );
                        break;
                    default:
                        PostFinanceCheckoutModule::log(Logger::WARNING, "Unkown alert loaded from database: " . array($row));
                }
            }
        }
        return $alerts;
    }
}

