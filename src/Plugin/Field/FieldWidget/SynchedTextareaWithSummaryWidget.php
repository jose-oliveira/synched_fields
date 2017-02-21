<?php

namespace Drupal\synched_fields\Plugin\Field\FieldWidget;

use Drupal\demo_language\LanguageHierarchy;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'synched_text_textarea_with_summary' widget.
 *
 * @FieldWidget(
 *   id = "synched_text_textarea_with_summary",
 *   label = @Translation("Synched Text area with a summary"),
 *   field_types = {
 *     "synched_text_with_summary"
 *   }
 * )
 */
class SynchedTextareaWithSummaryWidget extends TextareaWithSummaryWidget  implements ContainerFactoryPluginInterface {


  protected $language_hierarchy;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, LanguageHierarchy $language_hierarchy) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->language_hierarchy = $language_hierarchy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('demo_language.language_hierarchy'));
  }

  /**
   * {@inheritdoc}
   */
  function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $is_leaf_current_language = $this->language_hierarchy->isLeaf($items->getLangcode());
    $is_fallback_synched = $this->getFallbackSynchChildrenValue($items, $delta);
    $synch_children = $this->getSynchChildrenValue($items, $delta);

    $element['#disabled'] = $is_fallback_synched;
    $element['#description'] = $is_fallback_synched ?
      t("You can't edit this field because it get its value from a parent translation.") : $element['#description'];

    $element['synch_children'] = array(
      '#type' => 'checkbox',
      '#default_value' => $synch_children,
      '#access' => !$is_leaf_current_language,
      '#title' => t('Synch children '),
      '#description' => t('Copy the value of this field to its children translations.'),
      '#weight' => 10,
    );

    return $element;
  }

  /**
   * By default, synch values should be FALSE for new translations.
   * @param $items
   * @param $delta
   * @return bool
   */
  protected function getSynchChildrenValue($items, $delta) {
    $is_new_translation = $items->getEntity()->isNewTranslation();
    return $items[$delta]->synch_children && !$is_new_translation;
  }

  protected function getFallbackSynchChildrenValue($items, $delta) {
    $fallback_langcode = $this->language_hierarchy->getLanguageFallback($items->getLangcode());
    $entity = $items->getEntity();
    if ($fallback_langcode && $entity->hasTranslation($fallback_langcode)) {
      $translation = $entity->getTranslation($fallback_langcode);
      $field_name = $items->getName();
      return !empty($translation->get($field_name)->getValue()[$delta]['synch_children']);
    }
    return FALSE;
  }
}
