<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 *//**
 * PostFinanceCheckout
 *
 * This module allows you to interact with the PostFinanceCheckout payment service using OXID eshop.
 * Using this module requires a PostFinanceCheckout account (https://www.postfinance-checkout.ch/user/signup)
 *
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category      module
 * @package       PostFinanceCheckout
 * @author        customweb GmbH
 * @link          commercialWebsiteUrl
 * @copyright (C) customweb GmbH 2018
 */

namespace Pfc\PostFinanceCheckout\Application\Controller\Admin;
require_once(OX_BASE_PATH . 'modules/pfc/PostFinanceCheckout/autoload.php');

use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class Alert.
 */
class Alert extends \oxadminview
{
    protected $_sThisTemplate = 'pfcPostFinanceCheckoutError.tpl';

    public function manualtask(){
        $url = PostFinanceCheckoutModule::settings()->getBaseUrl() . '/s/' . PostFinanceCheckoutModule::settings()->getSpaceId() . '/manual-task/list';
        \oxregistry::getUtils()->redirect($url);
        die();
    }
}