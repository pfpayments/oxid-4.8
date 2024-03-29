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

namespace Pfc\PostFinanceCheckout\Application\Controller\Admin;
require_once(OX_BASE_PATH . 'modules/pfc/PostFinanceCheckout/autoload.php');

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\RefundState;
use PostFinanceCheckout\Sdk\Model\TransactionCompletionState;
use PostFinanceCheckout\Sdk\Model\TransactionVoidState;
use Pfc\PostFinanceCheckout\Core\Exception\OptimisticLockingException;
use Pfc\PostFinanceCheckout\Core\Service\CompletionService;
use Pfc\PostFinanceCheckout\Core\Service\RefundService;
use Pfc\PostFinanceCheckout\Core\Service\VoidService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;


/**
 * Class Transaction.
 */
class Transaction extends \oxadmindetails
{

    /**
     * Controller template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'pfcPostFinanceCheckoutTransaction.tpl';

    /**
     * @return string
     */
    public function render()
    {
        parent::render();
        $this->_aViewData['pfc_postFinanceCheckout_enabled'] = false;
        $orderId = $this->getEditObjectId();
        try {
            if ($orderId != '-1' && isset($orderId)) {
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);                $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
                /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
                if ($transaction->loadByOrder($orderId)) {
                    $transaction->pull();
                    $this->_aViewData['labelGroupings'] = $transaction->getLabels();
                    $this->_aViewData['pfc_postFinanceCheckout_enabled'] = true;
                    return $this->_sThisTemplate;
                } else {
                    throw new \Exception(PostFinanceCheckoutModule::instance()->translate('Not a PostFinance Checkout order.'));
                }
            } else {
                throw new \Exception(PostFinanceCheckoutModule::instance()->translate('No order selected'));
            }
        } catch (OptimisticLockingException $e) {
            $this->_aViewData['pfc_postFinanceCheckout_enabled'] = $e->getMessage();
            return $this->_sThisTemplate;
        } catch (\Exception $e) {
            $this->_aViewData['pfc_error'] = $e->getMessage();
            return 'pfcPostFinanceCheckoutError.tpl';
        }
    }

    /**
     * Creates and sends a completion job.
     */
    public function complete()
    {
    	PostFinanceCheckoutModule::log(Logger::DEBUG, "Start complete.");
        $oxid = $this->getEditObjectId();
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByOrder($oxid)) {
        	PostFinanceCheckoutModule::log(Logger::DEBUG, "Loaded by order.");
            try {
            	$transaction->updateLineItems();
            	PostFinanceCheckoutModule::log(Logger::DEBUG, "Updated items.");
            	$job = CompletionService::instance()->create($transaction);
            	PostFinanceCheckoutModule::log(Logger::DEBUG, "Created job.");
            	CompletionService::instance()->send($job);
            	PostFinanceCheckoutModule::log(Logger::DEBUG, "Sent job.");
                if ($job->getState() === TransactionCompletionState::FAILED) {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($job->getFailureReason());
                } else {
                    $this->_aViewData['message'] = PostFinanceCheckoutModule::instance()->translate("Successfully created and sent completion job !id.", true, array('!id' => $job->getJobId()));
                }
            } catch (\Exception $e) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Exception occurred while completing transaction: {$e->getMessage()} - {$e->getTraceAsString()}");
                PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($e->getMessage()); // To set error
            }
        } else {
            $error = "Unable to load transaction by order $oxid for completion.";
            PostFinanceCheckoutModule::log(Logger::ERROR, $error);
            PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($error); // To set error
        }
    }

    /**
     * Creates and sends a void job.
     *
     */
    public function void()
    {
    	PostFinanceCheckoutModule::log(Logger::DEBUG, "Start void.");
        $oxid = $this->getEditObjectId();
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByOrder($oxid)) {
        	PostFinanceCheckoutModule::log(Logger::DEBUG, "Loaded by order.");
        	try {
        		$transaction->pull();
        		$job = VoidService::instance()->create($transaction);
        		PostFinanceCheckoutModule::log(Logger::DEBUG, "Created job.");
        		VoidService::instance()->send($job);
        		PostFinanceCheckoutModule::log(Logger::DEBUG, "Sent job.");
                if ($job->getState() === TransactionVoidState::FAILED) {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($job->getFailureReason());
                } else {
                    $this->_aViewData['message'] = PostFinanceCheckoutModule::instance()->translate("Successfully created and sent void job !id.", true, array('!id' => $job->getJobId()));
                }
            } catch (\Exception $e) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Exception occurred while completing transaction: {$e->getMessage()} - {$e->getTraceAsString()}");
                PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($e->getMessage()); // To set error
            }
        } else {
            $error = "Unable to load transaction by order $oxid for completion.";
            PostFinanceCheckoutModule::log(Logger::ERROR, $error);
            PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($error); // To set error
        }
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for completion, and checks if any completion jobs are currently running.
     *
     * @param $orderId
     * @return bool
     */
    public function canComplete($orderId)
    {
        try {
class_exists(\Pfc\PostFinanceCheckout\Application\Model\CompletionJob::class);        	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\CompletionJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\CompletionJob */
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(TransactionCompletionState::PENDING)) &&
                in_array($transaction->getState(), CompletionService::instance()->getSupportedTransactionStates());
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check completion possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for refund, and checks if any refund jobs are currently running.
     *
     * @param $orderId
     * @return bool
     */
    public function canRefund($orderId)
    {
        try {
class_exists(\Pfc\PostFinanceCheckout\Application\Model\RefundJob::class);            $job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\RefundJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\RefundJob */
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(RefundState::MANUAL_CHECK, RefundState::PENDING)) &&
                in_array($transaction->getState(), RefundService::instance()->getSupportedTransactionStates()) && !empty(RefundService::instance()->getReducedItems($transaction));
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check completion possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for void, and checks if any void jobs are currently running.
     * @param $orderId
     * @return bool
     */
    public function canVoid($orderId)
    {
        try {
class_exists(\Pfc\PostFinanceCheckout\Application\Model\VoidJob::class);        	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\VoidJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\VoidJob */
class_exists(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(TransactionVoidState::PENDING)) &&
                in_array($transaction->getState(), VoidService::instance()->getSupportedTransactionStates());
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check void possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }
}