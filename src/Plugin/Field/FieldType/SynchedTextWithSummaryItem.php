<?php

namespace Drupal\synched_fields\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\text\Plugin\Field\FieldType\TextWithSummaryItem;

/**
 * Plugin implementation of the 'synched_text_with_summary' field type.
 *
 * @FieldType(
 *   id = "synched_text_with_summary",
 *   label = @Translation("Synched Text (formatted, long, with summary)"),
 *   description = @Translation("This field stores long text with a format, an optional summary and optionally can synch its value to its children."),
 *   category = @Translation("Text"),
 *   default_widget = "text_textarea_with_summary",
 *   default_formatter = "text_default"
 * )
 */
class SynchedTextWithSummaryItem extends TextWithSummaryItem {

  use SynchedFieldTypeTrait;

  /**
   * @var
   */
  protected $language_hierarchy;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Drupal\Core\TypedData\DataDefinitionInterface $definition, $name, \Drupal\Core\TypedData\TypedDataInterface $parent) {
    parent::__construct($definition, $name, $parent);
    $this->language_hierarchy = \Drupal::service('demo_language.language_hierarchy');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = self::getPropertyDefinitions(parent::propertyDefinitions($field_definition));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = self::getSchema(parent::schema($field_definition));
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    $this->doPreSave();
  }
}
