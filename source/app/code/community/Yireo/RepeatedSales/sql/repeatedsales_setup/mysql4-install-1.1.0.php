<?php
/**
 * Yireo RepeatedSales for Magento
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License
 */

/** @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'previous_order', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Previous Order',
    'input'             => 'boolean',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable,virtual,bundle,grouped',
    'configurable'      => false,
    'used_for_promo_rules' => true,
    'is_configurable'      => false,
    'is_used_for_promo_rules' => true
));

$installer->addAttribute('catalog_product', 'any_previous_order', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Any Previous Order',
    'input'             => 'boolean',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable,virtual,bundle,grouped',
    'configurable'      => false,
    'used_for_promo_rules' => true,
    'is_configurable'      => false,
    'is_used_for_promo_rules' => true
));

$installer->endSetup();
