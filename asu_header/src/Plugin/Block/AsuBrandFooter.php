<?php

namespace Drupal\asu_header\Plugin\Block;


/**
 * Provides an ASU footer block.
 *
 * @Block(
 *  id = "asu_brand_footer",
 *  admin_label = @Translation("ASU Brand: Footer"),
 * )
 */
class AsuBrandFooter extends AsuBrandBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $basepath = ASU_BRAND_HEADER_BASEPATH_DEFAULT;
    $version = '4.8';//ASU_BRAND_HEADER_VERSION_DEFAULT;
    $template_key = ASU_BRAND_HEADER_TEMPLATE_DEFAULT;

    $uri = "{$basepath}/{$version}/includes/footer.shtml";

    $build = [];
    $build['footer'] = [
      '#type' => 'inline_template',
      '#template' => '{{ html | raw }}',
      '#context' => [
        'html' => $this->fetchExternalMarkUp($uri),
      ]
    ];
    $build['#attached']['library'][] = 'asu_brand/header';
    return $build;
  }

}
