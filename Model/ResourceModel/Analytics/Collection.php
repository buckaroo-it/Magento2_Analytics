<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Model\ResourceModel\Analytics;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'analytics_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Buckaroo\Magento2Analytics\Model\Analytics::class,
            \Buckaroo\Magento2Analytics\Model\ResourceModel\Analytics::class
        );
    }
}
