<?php

/**
 * Default properties for the setPlaceholders snippet
 * @author Jason Grant
 * 12/30/12
 *
 * @package setplaceholders
 * @subpackage build
 */

$properties = array(
    array(
          'name' => 'delimiter',
          'desc' => 'prop_sph.delimiter_desc',
          'type' => 'textfield',
          'options' => '',
          'value' => ',',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'fields',
          'desc' => 'prop_sph.fields_desc',
          'type' => 'textfield',
          'options' => '',
          'value' => '',
          'area' => 'Deprecated',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'id',
          'desc' => 'prop_sph.id_desc',
          'type' => 'integer',
          'options' => '',
          'value' => '',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'output',
          'desc' => 'prop_sph.output_desc',
          'type' => 'combo-boolean',
          'options' => '',
          'value' => '0',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'placeholders',
          'desc' => 'prop_sph.placeholders_desc',
          'type' => 'textfield',
          'options' => '',
          'value' => '',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'prefix',
          'desc' => 'prop_sph.prefix_desc',
          'type' => 'textfield',
          'options' => '',
          'value' => 'sph.',
          'lexicon' => 'setplaceholders:default'
          ),
    array(
          'name' => 'ph',
          'desc' => 'prop_sph.ph_desc',
          'type' => 'textfield',
          'options' => '',
          'value' => '',
          'lexicon' => 'setplaceholders:default'
          )
);

return $properties;