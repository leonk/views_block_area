<?php

/**
 * @file
 * Contains \Drupal\block\Plugin\views\area\Block.
 */

namespace Drupal\views_block_area\Plugin\views\area;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Provides an area handler which renders a block entity in a certain view mode.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("views_block_area")
 */
class ViewsBlockArea extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['block_id'] = ['default' => NULL];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $options = [];
    /** @var \Drupal\views_block_area\ViewsBlockAreaManagerInterface $views_block_area_manager */
    $views_block_area_manager = \Drupal::service('views_block_area.manager');
    $definitions = $views_block_area_manager->getBlockDefinitions();
    foreach ($definitions as $id => $definition) {
      // If allowed plugin ids are set then check that this block should be
      // included.
      $category = (string) $definition['category'];
      $options[$category][$id] = $definition['admin_label'];
    }

    $form['block_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Block'),
      '#options' => $options,
      '#empty_option' => $this->t('Please select'),
      '#default_value' => $this->options['block_id'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $element = [];
    /** @var \Drupal\block_field\BlockFieldItemInterface $item */
    $block_instance = $this->getBlock();
    // Make sure the block exists and is accessible.
    if (!$block_instance || !$block_instance->access(\Drupal::currentUser())) {
      return NULL;
    }

    // @see \Drupal\block\BlockViewBuilder::buildPreRenderableBlock
    // @see template_preprocess_block()
    $element = [
      '#theme' => 'block',
      '#attributes' => [],
      '#configuration' => $block_instance->getConfiguration(),
      '#plugin_id' => $block_instance->getPluginId(),
      '#base_plugin_id' => $block_instance->getBaseId(),
      '#derivative_plugin_id' => $block_instance->getDerivativeId(),
      '#id' => $block_instance->getPluginId(),
      'content' => $block_instance->build(),
    ];
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $renderer->addCacheableDependency($element, $block_instance);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBlock() {
    if (empty($this->options['block_id'])) {
      return NULL;
    }

    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = \Drupal::service('plugin.manager.block');

    /** @var \Drupal\Core\Block\BlockPluginInterface $block_instance */
    $block_instance = $block_manager->createInstance($this->options['block_id'], []);

    $plugin_definition = $block_instance->getPluginDefinition();

    // Don't return broken block plugin instances.
    if ($plugin_definition['id'] == 'broken') {
      return NULL;
    }

    // Don't return broken block content instances.
    if ($plugin_definition['id'] == 'block_content') {
      $uuid = $block_instance->getDerivativeId();
      if (!\Drupal::entityManager()->loadEntityByUuid('block_content', $uuid)) {
        return NULL;
      }
    }

    return $block_instance;
  }
}
