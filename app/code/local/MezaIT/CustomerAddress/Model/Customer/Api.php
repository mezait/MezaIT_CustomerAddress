<?php
class MezaIT_CustomerAddress_Model_Customer_Api extends Mage_Api_Model_Resource_Abstract {
    // Customer map
    private $customerMap = array(
        'entity_id'         => 'customer_id',
        'firstname'         => 'firstname',
        'lastname'          => 'lastname',
        'email'             => 'email',
        'created_at'        => 'created_at',
        'updated_at'        => 'updated_at');

    // Customer address map
    private $addressMap = array(
        'billing_id'            => 'customer_address_id',
        'billing_street'        => 'street',
        'billing_city'          => 'city',
        'billing_region_id'     => 'region_id',
        'billing_postcode'      => 'postcode',
        'billing_country_id'    => 'country_id',
        'billing_telephone'     => 'telephone',
        'billing_created_at'    => 'created_at',
        'billing_updated_at'    => 'updated_at');

    /**
     * Prepare the customer collection
     *
     * @param   null|object|array   $filters    Filters to match the customers
     *
     * @return  array   Customer collection
     */
    private function _prepareCollection($filters = null) {
        $helper = Mage::helper('mezait_customeraddress');

        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_id', 'customer_address/entity_id', 'default_billing', null, 'left')
            ->joinAttribute('billing_street', 'customer_address/street', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_region_id', 'customer_address/region_id', 'default_billing', null, 'left')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_created_at', 'customer_address/created_at', 'default_billing', null, 'left')
            ->joinAttribute('billing_updated_at', 'customer_address/updated_at', 'default_billing', null, 'left');

        $filters = $helper->parseFilters($filters);

        try {
            foreach($filters as $filter) {
                $collection->addAttributeToFilter($filter);
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }

        return $collection;
    }

    /**
     * Number of customers
     *
     * @param   null|object|array   $filters    Filters that match the customers
     *
     * @return  int     The number of customers
     */
    public function count($filters = null) {
        $collection = $this->_prepareCollection($filters);

        if (Mage::getStoreConfig('mezait_customeraddress/debug/log_sql') == 1)
            Mage::log('Customer with address count: '.(string) $collection->getSelectCountSql());

        return $collection->getSize();
    }

    /**
     * Retrieve a list of customers and addresses
     *
     * @param   null|object|array   $filters    Filters that match the customers
     * @param   int                 $pageNum    Page number
     * @param   int                 $pageSize   Page size
     *
     * @return array    List of customers
     */
    public function pagedList($filters = null, $pageNum = 1, $pageSize = 10) {
        $helper = Mage::helper('mezait_customeraddress');

        $collection = $this->_prepareCollection($filters)
            ->setPage($pageNum, $pageSize)
            ->load();

        if (Mage::getStoreConfig('mezait_customeraddress/debug/log_sql') == 1)
            Mage::log('Customer with address paged list: '.(string) $collection->getSelect());

        $result = array();

        foreach ($collection as $customer) {
            $result[] = array(
                'customer' => $helper->parseArray($this->customerMap, $customer),
                'address' => $helper->parseArray($this->addressMap, $customer));
        }

        return $result;
    }
}