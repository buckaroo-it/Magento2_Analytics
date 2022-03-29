<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * It is available through the world-wide-web at this URL:
 * https://tldrlegal.com/license/mit-license
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@buckaroo.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@buckaroo.nl for more information.
 *
 * @copyright Copyright (c) Buckaroo B.V.
 * @license   https://tldrlegal.com/license/mit-license
 */

namespace Buckaroo\Magento2Analytics\Controller\Redirect;

class Process extends \Buckaroo\Magento2\Controller\Redirect\Process
{
    /**
     * Redirect to Success url, which means everything seems to be going fine
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirectSuccess()
    {
        $this->logger->addDebug(__METHOD__ . '|1|');

        $store = $this->order->getStore();

        /**
         * @noinspection PhpUndefinedMethodInspection
         */

        $url = $this->accountConfig->getSuccessRedirect($store);
        
        $successMessage = __('Your order has been placed successfully.');
        if (method_exists($this, 'addSuccessMessage')) {
            $this->addSuccessMessage($successMessage);
        } else {
            $this->messageManager->addSuccessMessage($successMessage);
        }

        $this->quote->setReservedOrderId(null);
        $this->customerSession->setSkipSecondChance(false);

        if (!empty($this->response['brq_payment_method'])
            &&
            ($this->response['brq_payment_method'] == 'applepay')
            &&
            !empty($this->response['brq_statuscode'])
            &&
            ($this->response['brq_statuscode'] == '190')
            &&
            !empty($this->response['brq_test'])
            &&
            ($this->response['brq_test'] == 'true')
        ) {
            $this->redirectSuccessApplePay();
        }

        $this->logger->addDebug(__METHOD__ . '|2|' . var_export($url, true));

        //add clientid - GA tracking
        $clientId = $this->getRequest()->getParam('clientId');


        $queryArguments = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryArguments);

        if ($clientId) {
            if (strpos($url, '?') !== false) {
                $url = substr($url, 0, strpos($url, '?'));
            }
            $queryArguments = array_merge($queryArguments, ["clientId" => $clientId]);
        }

        if (method_exists($this, 'handleProcessedResponse')) {
            return $this->handleProcessedResponse($url, ['_query' =>$queryArguments]);
        }
        return $this->_redirect($url, ['_query' =>$queryArguments]);
    }
}
