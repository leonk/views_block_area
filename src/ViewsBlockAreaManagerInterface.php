<?php

/**
 * @file
 * Contains \Drupal\block_field\BlockFieldManagerInterface.
 */

namespace Drupal\views_block_area;

/**
 * Provides an interface defining a BLock field manager.
 */
interface ViewsBlockAreaManagerInterface {

  /**
   * Get sorted listed of supported block definitions.
   *
   * @return array
   *   An associative array of supported block definitions.
   */
  public function getBlockDefinitions();

}
