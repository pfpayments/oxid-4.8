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
namespace Pfc\PostFinanceCheckout\Core\Webhook;
require_once(OX_BASE_PATH . 'modules/pfc/PostFinanceCheckout/autoload.php');

/**
 * Webhook processor to handle manual task state transitions.
 */
class ManualTask extends AbstractWebhook {

    /**
     * Updates the number of open manual tasks.
     *
     * @param \Pfc\PostFinanceCheckout\Core\Webhook\Request $request
     */
    public function process(Request $request){
        \Pfc\PostFinanceCheckout\Core\Service\ManualTask::instance()->update();
    }
}