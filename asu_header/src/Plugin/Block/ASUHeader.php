<?php

namespace Drupal\asu_header\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\system\Entity\Menu;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'ASU Custom Header' block.
 *
 * @Block(
 *   id = "rfi_custom_block",
 *   admin_label = @Translation("ASU Custom Header"),
 * )
 */

 
class ASUHeader extends BlockBase {

  public function defaultConfiguration() {
    return [
      'menu_injection_flag' => 1,
      'menu_name' => ASU_BRAND_SITE_MENU_NAME_DEFAULT,
    ] + parent::defaultConfiguration();

  }
  /**
    * {@inheritdoc}
    */
  public function blockForm($form, FormStateInterface $formState) {
    $form['site_menu'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Site menu injection'),
      '#collapsed' => FALSE,
      '#weight' => 4,
    ];

    $form['site_menu']['menu_injection_flag'] = [
      '#type' => 'checkbox',
      '#title' => t('Append local site menu into ASU header menu and display in responsive state.'),
      '#default_value' =>  $this->configuration['menu_injection_flag'],
    ];

    $form['site_menu']['menu_name'] = [
      '#type' => 'select',
      '#title' => t('Menu to inject'),
      '#description' => t('Select the site menu to inject.'),
      '#options' => $this->get_menus(),
      '#default_value' => $this->configuration['menu_name'],
      '#states' => [
        'visible' => [
          ':input[name="settings[site_menu][menu_injection_flag]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['image'] = array(
      '#type' => 'managed_file',
      '#upload_location' => 'public://upload/asu_header',
      '#title' => t('Header ASU logo'),
      '#upload_validators' => [
          'file_validate_extensions' => ['jpg', 'jpeg', 'png', 'gif']
      ],
      '#default_value' => isset($this->configuration['image']) ? $this->configuration['image'] : '',
      '#description' => t('The image to display on the brand header'),
      '#required' => true
  );
  $form['mobileimage'] = array(
    '#type' => 'managed_file',
    '#upload_location' => 'public://upload/asu_header',
    '#title' => t('Header ASU logo for Mobile'),
    '#upload_validators' => [
        'file_validate_extensions' => ['jpg', 'jpeg', 'png', 'gif']
    ],
    '#default_value' => isset($this->configuration['mobileimage']) ? $this->configuration['mobileimage'] : '',
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
       $mobileimage = $formState->getValue('mobileimage');
       if ($mobileimage != $this->configuration['mobileimage']) {
         if (!empty($mobileimage[0])) {
           $mobfile = File::load($mobileimage[0]);
           $mobfile->setPermanent();
           $mobfile->save();
         }
       }
    $this->configuration['image'] = $formState->getValue('image');
    $this->configuration['mobileimage'] = $formState->getValue('mobileimage');
    $this->configuration['menu_injection_flag'] = $formState->getValue(['site_menu', 'menu_injection_flag']);
    $this->configuration['menu_name'] = $formState->getValue(['site_menu', 'menu_name']);
  }

  /**
   * {@inheritdoc}
   */
  
  public function build() {
    $image = $this->configuration['image'];
    $file = File::load($image[0]);
    $mobimage = $this->configuration['mobileimage'];
    $mobdile = File::load($mobimage[0]);
    $js_settings = $this->getJsSettings();
    $site_name = \Drupal::config('system.site')->get('name');
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $inject_menu = $this->configuration['menu_injection_flag'];
    if ($inject_menu) {
      $menu_name = $this->configuration['menu_name'];
      $menu_items = $this->get_menu_items($menu_name);
    }

    return [
      '#theme' => 'asu-header',
      '#title' => 'ASU Custom Header',
      '#description' => 'The ASU header for CFO site',
      '#user' => $user->get('name')->value,
      '#sign_out_url' => $js_settings['asu_sso_signouturl'],
      '#sign_in_url' => $js_settings['asu_sso_signinurl'],
      '#is_signed_in' => $js_settings['asu_sso_signedin'],
      '#logo' => $file->createFileUrl(),
      '#sitename' => $site_name,
      '#logohor' => $mobdile->createFileUrl(),
      '#menuitems' => $menu_items,
      '#attached' => [
        'library' => [
          'asu_header/asu-header',
        ],
      ],
    ];
      }
       /**
   * Get ASU brand block settings.
   */
  private function getJsSettings() {

    $is_user_logged_in = TRUE;
    $moduleHandler = \Drupal::service('module_handler');

    if (\Drupal::currentUser()->isAnonymous()) {
      $is_user_logged_in = FALSE;
    }

    // Set javascript settings.
    $js_settings = [
      'asu_sso_signedin' => ($is_user_logged_in ? 'true' : 'false'),
      'asu_sso_signinurl' => '',
      'asu_sso_signouturl' => '',
    ];

    // NOTE: Since we're currently relying on Drupal core's Dynamic Page Cache module
    // (enabled by default in most sites) to cache ASU Brand blocks, appending a destination query
    // to the Sing In path won't work correctly. This is because the path with
    // the destination query will be cached, and it will be the same on all pages,
    // which is not desired.

    // Alter the signin/signout URL if cas is enabled.
    if ($moduleHandler->moduleExists('cas')){
      $cas_sign_in_path = \Drupal::config('cas.settings')->get('server.path');
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput($cas_sign_in_path, ['absolute' => TRUE, 'https' => TRUE])->toString();
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('/caslogout', ['absolute' => TRUE])->toString();
    } else {
      $js_settings['asu_sso_signinurl'] = Url::fromUserInput('/user/login', ['absolute' => TRUE])->toString();
      $js_settings['asu_sso_signouturl'] = Url::fromUserInput('/user/logout', ['absolute' => TRUE])->toString();
    }

    return $js_settings;
  }

  private function get_menus() {
    $all_menus = Menu::loadMultiple();
    $menus = [];
    foreach ($all_menus as $id => $menu) {
      $menus[$id] = $menu->label();
    }

    return $menus;
  }
  private function get_menu_items($menu_name) {
    $menu_tree = \Drupal::menuTree();
    // Build the typical default set of menu tree parameters.
    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(3);
    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);
    $menu = [];

    //Transform the tree using the manipulators you want.
    $manipulators = [
      // Only show links that are accessible for the current user.
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      // Use the default sorting of menu links.
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
   // Finally, build a renderable array from the transformed tree.
   $menu_tmp = $menu_tree->build($tree);
    foreach ($menu_tmp['#items'] as $item) {
      $top_level = $this->get_menu_item($item);
     
      if (!empty($item['below'])) {
        foreach ($item['below'] as $child) {
          $second_level = $this->get_menu_item($child);
          if (!empty($child['below'])) {
            foreach ($child['below'] as $grandchild) {
              $second_level['children'][] = $this->get_menu_item($grandchild);
            }
          }
          $top_level['children'][] = $second_level;
        }
      }
      $menu[] = $top_level;
    }
    return $menu;

  }
  function get_menu_item($item) {  
    $menu_item = ['title' => $item['title'], 'path' => $item['url']->toString(), 'class' => is_null($item['url']->getOptions()['attributes']['class'])? 'no-break-class' : $item['url']->getOptions()['attributes']['class']];
    return $menu_item;
  }

  

  // public function getCacheContexts() {
  //   //if you depends on \Drupal::routeMatch()
  //   //you must set context of this block with 'route' context tag.
  //   //Every new route this block will rebuild
  //   return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  // }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
  	return Cache::mergeContexts(
  		parent::getCacheContexts(),
  		['user']
  	);
  }

}