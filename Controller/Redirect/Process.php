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

use Magento\Framework\App\ResponseInterface;

class Process extends \Buckaroo\Magento2\Controller\Redirect\Process
{
    /**
     * Redirect to Success url, which means everything seems to be going fine
     *
     * @return ResponseInterface
     */
    protected function redirectSuccess(): ResponseInterface
    {
        $this->eventManager->dispatch('buckaroo_process_redirect_success_before');

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

        $this->redirectSuccessApplePay();

        $this->logger->addDebug(sprintf(
            '[REDIRECT - %s] | [Controller] | [%s:%s] - Redirect Success | redirectURL: %s',
            $this->payment->getMethod(),
            __METHOD__,
            __LINE__,
            $url,
        ));

        $queryArguments = [];
        parse_str((string)parse_url($url, PHP_URL_QUERY), $queryArguments);

        $filteredQueryArguments = [];
        if (class_exists(\Buckaroo\Magento2Analytics\Service\CookieParamService::class)) {
            $cookieParamService = $this->_objectManager->get(
                \Buckaroo\Magento2Analytics\Service\CookieParamService::class
            );

            $filteredQueryArguments = $cookieParamService->getQueryArgumentsByCookies($this->getRequest()->getParams());
        }

        if (!empty($filteredQueryArguments)) {
            if (strpos($url, '?') !== false) {
                $url = substr($url, 0, strpos($url, '?'));
            }
            $queryArguments = array_merge($queryArguments, $filteredQueryArguments);
        }

        if (method_exists($this, 'handleProcessedResponse')) {
            return $this->handleProcessedResponse($url, ['_query' =>$queryArguments]);
        }
        return $this->_redirect($url, ['_query' =>$queryArguments]);
    }
}
