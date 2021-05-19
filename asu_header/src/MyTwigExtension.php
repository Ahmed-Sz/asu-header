<?php

namespace Drupal\asu_header;
use Drupal\block\Entity\Block;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

class MyTwigExtension extends \Twig_Extension {
      /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'block_display';
  }
 
  /**
   * In this function we can declare the extension function.
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('updateChildernMenu', array($this, 'updateChildernMenu'), array('is_safe' => array('html'))),
      new \Twig_SimpleFunction('getasuheader', array($this, 'getasuheader'), array('is_safe' => array('html'))),
      new \Twig_SimpleFunction('getContentsBeforeasuheader', array($this, 'getContentsBeforeasuheader'), array('is_safe' => array('html'))),
    );
  }
    public function updateChildernMenu($children) {
        $arrayToReturn = [];
        $tempArray = [];
        foreach ($children as $child) {
            if ($child['class'] === "no-break-class") {
                array_push($tempArray,$child);
              } 
              else {
                array_push($arrayToReturn,$tempArray);
                $tempArray = [];
                array_push($tempArray,$child);
              }
            }
          if(!empty($tempArray)) {
            array_push($arrayToReturn,$tempArray);
          }
            return $arrayToReturn;
        }

        public function getasuheader(){
          return file_get_contents('https://www.asu.edu/asuthemes/5.0/headers/component.html'); 
        }
        public function getContentsBeforeasuheader(){
          return file_get_contents('https://www.asu.edu/asuthemes/5.0/heads/component-head.html'); 
        }
}