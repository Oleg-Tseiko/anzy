<?php

namespace Drupal\anzy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for the Anzy module.
 */
class AnzyController extends ControllerBase {

  /**
   * Form build interface.
   *
   * @var Drupal\Core\Form\FormBase
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * Return form for review.
   */
  public function form() {
    $form = $this->formBuilder->getForm('\Drupal\anzy\Form\GbookForm');
    return $form;
  }

  /**
   * Get all reviews for page.
   *
   * @return array
   *   A simple array.
   */
  public function load() {
    $connection = \Drupal::service('database');
    $query = $connection->select('anzy', 'a');
    $query->fields('a',
      ['name', 'comment', 'phone', 'mail', 'created', 'image', 'avatar', 'id']
    );
    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Render all reviews entries.
   */
  public function report() {
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    $form = $this->form();
    $rows = [];
    $dest = $this->getDestinationArray();
    foreach ($info as &$value) {
      $fid = $value['image'];
      $file = File::load($fid);
      $value['image'] = !empty($file) ? file_url_transform_relative(file_create_url($file->getFileUri())) : '';
      $avafid = $value['avatar'];
      $avafile = File::load($avafid);
      $value['avatar'] = !empty($avafile) ? file_url_transform_relative(file_create_url($avafile->getFileUri())) : '';
      array_push($rows, $value);
    }
    $form['#attached']['library'][] = 'anzy/my-lib';
    return [
      '#theme' => 'Gbook_template',
      '#items' => $rows,
      '#form' => $form,
      '#dest' => $dest['destination'],
    ];
  }

}
