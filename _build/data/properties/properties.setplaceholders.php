<?php

/**
 * Default properties for the setPlaceholders snippet
 * @author Jason Grant
 *
 * @package setplaceholders
 * @subpackage build
 */

$properties = array(
	array(
		'name' => 'id',
		'desc' => 'prop_sph.id_desc',
		'type' => 'integer',
		'options' => '',
		'value' => '',
		'area' => 'Input',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'placeholders',
		'desc' => 'prop_sph.placeholders_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => '',
		'area' => 'Input',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'ph',
		'desc' => 'prop_sph.ph_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => '',
		'area' => 'Input',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'delimiter',
		'desc' => 'prop_sph.delimiter_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => ',',
		'area' => 'Output',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'output',
		'desc' => 'prop_sph.output_desc',
		'type' => 'combo-boolean',
		'options' => '',
		'value' => '0',
		'area' => 'Output',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'prefix',
		'desc' => 'prop_sph.prefix_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => 'sph.',
		'area' => 'Output',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'processTVs',
		'desc' => 'prop_sph.processTVs_desc',
		'type' => 'combo-boolean',
		'options' => '',
		'value' => '0',
		'area' => 'Output',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'sortby',
		'desc' => 'prop_sph.sortby_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => 'menuindex',
		'area' => 'Child/Sibling Selectors',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'sortdir',
		'desc' => 'prop_sph.sortdir_desc',
		'type' => 'list',
		'options' => array(
			array(
				'name' => 'ASC',
				'value' => 'ASC',
				'menu' => '',
			),
			array(
				'name' => 'DESC',
				'value' => 'DESC',
				'menu' => '',
			)
		),
		'value' => 'ASC',
		'area' => 'Child/Sibling Selectors',
		'lexicon' => 'setplaceholders:default'
	),
	array(
		'name' => 'staticCache',
		'desc' => 'prop_sph.staticCache_desc',
		'type' => 'list',
		'options' => array(
			array(
				'name' => 'On',
				'value' => '1',
				'menu' => '',
			),
			array(
				'name' => 'Off',
				'value' => '0',
				'menu' => '',
			)
		),
		'value' => '0',
		'area' => 'Caching',
		'lexicon' => 'setplaceholders:default'
	)
);

return $properties;
