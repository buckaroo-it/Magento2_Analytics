<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Model\ConfigProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Analytics
{
    private const XML_PATH_ANALYTICS_ENABLE_GA_CLIENT_ID_TRACKING
        = 'buckaroo_magento2/analytics/enable_ga_client_id_tracking';

    /**
     * @var ScopeConfigInterface
     */
    private $storeConfig;

    public function __construct(ScopeConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    public function isClientIdTrackingEnabled(): bool
    {
        $config = $this->storeConfig->getValue(
            static::XML_PATH_ANALYTICS_ENABLE_GA_CLIENT_ID_TRACKING,
            ScopeInterface::SCOPE_STORES
        );
        return (bool) $config;
    }
}
