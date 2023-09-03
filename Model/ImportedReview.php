<?php

namespace Kamephis\YotpoImporter\Model;

use Magento\Framework\Model\AbstractModel;

class ImportedReview extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Kamephis\YotpoImporter\Model\ResourceModel\ImportedReview::class);
    }
}
