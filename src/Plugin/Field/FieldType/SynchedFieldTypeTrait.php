<?php

namespace Drupal\synched_fields\Plugin\Field\FieldType;

use Drupal\Core\TypedData\DataDefinition;

/**
 * Trait for 'synched' configurable field types.
 */
trait SynchedFieldTypeTrait {

  /**
   * {@inheritdoc}
   */
  protected static function getPropertyDefinitions(array $properties) {
    $properties['synch_children'] = DataDefinition::create('boolean')
      ->setLabel(t('Synch Children'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getSchema(array $schema) {

    $schema['columns']['synch_children'] = array(
      'type' => 'int',
      'length' => 1,
      'default' => 0,
      'not null' => TRUE,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPreSave() {
    try {
      $this->synchFields();
    }catch(\InvalidArgumentException $e) {
      drupal_set_message(t('Error synching the field value. Make sure you have the same number of fields in every translation.'), 'error');
      $this->rollBackSynchChildrenValue();
    }
  }

  /**
   *
   */
  protected function rollBackSynchChildrenValue() {
    $field_synch_rollback = $this->getValue();
    $field_synch_rollback['synch_children'] = 0;
    $this->setValue($field_synch_rollback);
  }

  /**
   * Synchs the field value if specified on the parent.
   */
  protected function synchFields(){
    $entity = $this->getEntity();
    $field_current_langcode = $this->getLangcode();
    $field_name = $this->getFieldDefinition()->getName();
    $delta = $this->getName();

    if ($this->getValue()['synch_children']) {
      $children_langcodes = $this->language_hierarchy->languageTree[$field_current_langcode];
      foreach ($children_langcodes as $child_langcode) {
        if ($entity->hasTranslation($child_langcode)) {
          $translation = $entity->getTranslation($child_langcode);
          $field = $translation->get($field_name);
          $field[$delta] = $this->getValue();
          $field->setValue($field->getValue());
        }
      }
    }

  }

}
