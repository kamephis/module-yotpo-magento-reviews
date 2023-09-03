<?php

namespace Kamephis\YotpoImporter\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ImportedReview extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('kamephis_yotpo_imported_reviews', 'entity_id');
    }
}
