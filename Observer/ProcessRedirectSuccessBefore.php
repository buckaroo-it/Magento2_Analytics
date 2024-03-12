<?php

namespace Buckaroo\Magento2Analytics\Observer;

use Buckaroo\Magento2Analytics\Service\CookieParamService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class ProcessRedirectSuccessBefore implements ObserverInterface
{
    private CookieParamService $cookieParamService;
    private RequestInterface $request;

    public function __construct(
        CookieParamService $cookieParamService,
        RequestInterface $request
    ) {
        $this->cookieParamService = $cookieParamService;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $queryArguments = $observer->getData('arguments');
        $url = $observer->getData('url');
        parse_str((string)parse_url($url, PHP_URL_QUERY), $queryArguments);

        $filteredQueryArguments = $this->cookieParamService->getQueryArgumentsByCookies($this->request->getParams());

        if (!empty($filteredQueryArguments)) {
            if (strpos($url, '?') !== false) {
                $url = substr($url, 0, strpos($url, '?'));
            }
            $queryArguments = array_merge($queryArguments, $filteredQueryArguments);
        }

        $observer->setData('arguments', $queryArguments);
        $observer->setData('url', $url);
    }
}