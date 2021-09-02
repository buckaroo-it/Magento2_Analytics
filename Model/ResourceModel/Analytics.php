<?php
declare(strict_types=1);

namespace Buckaroo\Magento2Analytics\Model\ResourceModel;

class Analytics extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('buckaroo_magento2_analytics', 'analytics_id');
    }
}
