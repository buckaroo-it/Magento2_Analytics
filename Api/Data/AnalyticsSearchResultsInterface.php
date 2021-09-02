<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Api\Data;

interface AnalyticsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Analytics list.
     * @return \Buckaroo\Magento2Analytics\Api\Data\AnalyticsInterface[]
     */
    public function getItems();

    /**
     * Set quote_id list.
     * @param \Buckaroo\Magento2Analytics\Api\Data\AnalyticsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
