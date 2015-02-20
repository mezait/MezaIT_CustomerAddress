<?php
class MezaIT_CustomerAddress_Helper_Data extends Mage_Api_Helper_Data {
    /**
     * Test if an object is a key value pair
     *
     * @param   object  $object Object to test
     *
     * @return  boolean True if the object is a key value pair
     */
    private function _isKeyValuePair($object) {
        return is_object($object) && isset($object->key) && isset($object->value);
    }

    /**
     * Build OR filter
     *
     * @param   object  $complexObject  Complex object
     *
     * @return  array|null  OR filter if it can be parsed, otherwise null
     */
    private function _buildOrFilter($complexObject) {
        if ($this->_isKeyValuePair($complexObject)) {
            $complexFilterValue = $complexObject->value;

            if ($this->_isKeyValuePair($complexFilterValue)) {
                $conditionName = $complexFilterValue->key;
                $conditionValue = $complexFilterValue->value;
                $this->formatFilterConditionValue($conditionName, $conditionValue);

                return array('attribute' => $complexObject->key, $conditionName => $conditionValue);
            }
        }

        return null;
    }

    /**
     * Build OR filters
     *
     * @param   object  $complexFilterArrays    Complex filter arrays
     *
     * @return  array|null  OR filters if they can be parsed, otherwise null
     */
    private function _buildOrFilters($complexFilterArrays) {
        if (is_object($complexFilterArrays) && isset($complexFilterArrays->complexObjectArray)) {
            $complexObjectArray = $complexFilterArrays->complexObjectArray;
            $orFilters = array();

            if (is_array($complexObjectArray)) {
                foreach($complexObjectArray as $complexObject) {
                    $filter = $this->_buildOrFilter($complexObject);

                    if ($filter != null)
                        $orFilters[] = $filter;
                }
            }
            else {
                $filter = $this->_buildOrFilter($complexObjectArray);

                if ($filter != null);
                    $orFilters[] = $filter;
            }

            return count($orFilters) > 0 ? $orFilters : null;
        }

        return null;
    }

    /**
     * Parse an array for the required attributes
     *
     * @param   array   $attributeMap   Map of attribute names in format: array('field_name_in_db' => 'field_name')
     * @param   array   $array          Array to parse
     *
     * @return  array   Parsed array
     */
    public function parseArray($attributeMap, $array) {
        $result = array();

        foreach ($attributeMap as $key => $value) {
            $result[$value] = $array[$key];
        }

        return $result;
    }

    /**
     * Parse filters
     *
     * @param null|object|array $filters Filters
     *
     * @return array AND filters
     */
    public function parseFilters($filters) {
        $andFilters = array();

        if ($this->isComplianceWSI()) {
            if (is_array($filters)) {
                foreach($filters as $complexFilterArrays) {
                    $orFilters = $this->_buildOrFilters($complexFilterArrays);

                    if ($orFilters != null)
                        $andFilters[] = $orFilters;
                }
            }
            else {
                $orFilters = $this->_buildOrFilters($filters);

                if ($orFilters != null)
                    $andFilters[] = $orFilters;
            }
        }
        else {
            // todo: Handle Non WS-I Compliance Mode
        }

        return $andFilters;
    }
}