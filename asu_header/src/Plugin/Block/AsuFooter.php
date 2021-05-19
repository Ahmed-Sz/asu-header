<?php
/**
 * @file
 * Contains \Drupal\freshman\Plugin\Block\BlockBase.
 */

namespace Drupal\asu_header\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ASU footer' block.
 *
 * @Block(
 *   id = "asu footer block",
 *   admin_label = @Translation("ASU footer block"),
 * )
 */
class AsuFooter extends BlockBase
{
      /**
    * {@inheritdoc}
    */
  public function blockForm($form, FormStateInterface $formState) {

        $form['image'] = array(
        '#type' => 'managed_file',
        '#upload_location' => 'public://upload/asu_header',
        '#title' => t('Footer ASU logo'),
        '#upload_validators' => [
            'file_validate_extensions' => ['jpg jpeg png gif'],
        ],
        '#default_value' => isset($this->configuration['image']) ? $this->configuration['image'] : '',
        '#description' => t('The image to display on the brand header'),
        '#required' => true
        );

        return $form;

    }
    /**
    * {@inheritdoc}
    */
    public function blockSubmit($form, FormStateInterface $formState) {
        // Save image as permanent.
        $image = $formState->getValue('image');
        if ($image != $this->configuration['image']) {
            if (!empty($image[0])) {
                $file = File::load($image[0]);
                $file->setPermanent();
                $file->save();
            }
        }
        $this->configuration['image'] = $formState->getValue('image');
    }



  /**
   * {@inheritdoc}
   */
    public function build()
    {
        $image = $this->configuration['image'];
        $file = File::load($image[0]);
        return [
            '#theme' => 'asu-footer',
            '#footerlogo' => $file->createFileUrl(),
            '#title' => 'ASU Custom Footer',
        ];
    }
}
