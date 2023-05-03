<?php

namespace Buckaroo\Magento2Analytics\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class CookieParamRows extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('cookie', [
            'label' => __('Cookie Name'),
            'class' => 'required-entry',
            'style' => 'width: 250px',
        ]);

        $this->addColumn('url_param', [
            'label' => __('Url Param Name'),
            'class' => 'required-entry',
            'style' => 'width: 250px',
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}