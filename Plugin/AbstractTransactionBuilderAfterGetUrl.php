<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Plugin;

use Buckaroo\Magento2Analytics\Service\CookieParamService;

class AbstractTransactionBuilderAfterGetUrl
{
    private CookieParamService $cookieParamService;

    public function __construct(
        CookieParamService $cookieParamService
    ) {
        $this->cookieParamService = $cookieParamService;
    }

    public function afterGetReturnUrl(
        $subject,
        $result
    ) {
        try {
            if (strpos($result, '?') !== false) {
                $result .= "&" . $this->cookieParamService->getUrlParamsByCookies();
            } else {
                $result .= "?" . $this->cookieParamService->getUrlParamsByCookies();
            }
            //phpcs:ignore
        } catch (\Exception $e) {
            //@todo log
        }
        return $result;
    }
}
