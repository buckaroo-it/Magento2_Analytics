<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Observer;

class Analytics implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Buckaroo\Magento2Analytics\Api\Data\AnalyticsInterface $analyticsModel,
        \Buckaroo\Magento2Analytics\Api\AnalyticsRepositoryInterface $analyticsRepository,
        \Buckaroo\Magento2Analytics\Model\ConfigProvider\Analytics $configProvider
    ) {
        $this->cookieManager = $cookieManager;
        $this->analyticsModel = $analyticsModel;
        $this->analyticsRepository = $analyticsRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        //check if this feature is enabled
        if (!$this->configProvider->isClientIdTrackingEnabled()) {
            return;
        }

        try {
            $quote  = $observer->getEvent()->getQuote();
            if ($quote) {
                $quote_id = $quote->getEntityId();
            }
        } catch (\Exception $e) {
            //@todo log
        }

        if (isset($quote_id)) {
            $ga_cookie = $this->cookieManager->getCookie(
                '_ga'
            );
            $this->analyticsModel->setQuoteId($quote_id);
            $this->analyticsModel->setClientId($ga_cookie);
            $this->analyticsRepository->save($this->analyticsModel);
        }
    }
}
