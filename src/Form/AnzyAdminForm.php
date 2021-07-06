<?php

namespace Drupal\anzy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Contains \Drupal\anzy\Form\GbookAdminForm.
 *
 * @file
 */

/**
 * Provides an Gbook form.
 */
class AnzyAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gbook_admin_form';
  }

  /**
   * Get all reviews fields for page.
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
   * Building tableselect form for deletion functionality.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /*
     * Decoding and reversing(sort descending) stdClass,
     * To access it values and show newest first.
     */
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    /*
     * Form markup for tableselect,
     * creating proper render array rows for tableselect.
     */
    $content['message'] = [
      '#markup' => $this->t('Below is a list of all reviews including username, email, image, avatar, phone number, comment and submission date.'),
    ];
    $headers = [
      t('Guest name'),
      t('Phone'),
      t('Avatar'),
      t('Comment'),
      t('Email'),
      t('Submitted'),
      t('Photo'),
      t('Delete'),
      t('Edit'),
    ];
    $rows = [];
    foreach ($info as &$value) {
      $renderer = \Drupal::service('renderer');
      $dest = $this->getDestinationArray();
      $dest = $dest['destination'];
      $newVal = [];
      /*
       * Store fid, load images and render it images manualy,
       * to render it in tableselect.
       */
      $fid = $value['image'];
      $avafid = $value['avatar'];
      $file = File::load($fid);
      $avafile = File::load($avafid);
      $id = $value['id'];
      $img = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'thumbnail',
        '#uri' => !empty($file) ? $file->getFileUri() : '',
      ];
      if (empty($avafile)) {
        $ava = [
          '#markup' => '<img src="/modules/custom/anzy/img/default-user-avatar-300x293.png"/>',
        ];
      }
      else {
        $ava = [
          '#type' => 'image',
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $avafile->getFileUri() ,
        ];
      }
      /*
       * Setting deletion and edit button property,
       * render them manually so they could appear in tableselect.
       */
      $delete = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/admin/anzy/gbookDel/$id?destination=$dest"),
        '#title' => $this->t('Delete'),
        '#attributes' => [
          'data-dialog-type' => ['modal'],
          'class' => ['button', 'use-ajax'],
        ],
      ];
      $edit = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/admin/anzy/gbookChange/$id?destination=$dest"),
        '#title' => $this->t('Edit'),
        '#attributes' => ['class' => ['button']],
      ];
      /*
       * Hidden field used to give id so it could be used to delete db entry.
       */
      $newId = [
        '#type' => 'hidden',
        '#value' => $id,
      ];
      /*
       * Pushing all values to new array,
       * so it could be rendered properly by tableselect.
       */
      $newVal[0] = $value['name'];
      $newVal[2] = $renderer->render($ava);
      $newVal[1] = $value['phone'];
      $newVal[3] = $value['comment'];
      $newVal[4] = $value['mail'];
      $newVal[5] = $value['created'];
      $newVal[6] = $renderer->render($img);
      $newVal[7] = $renderer->render($delete);
      $newVal[8] = $renderer->render($edit);
      $newVal[9] = $newId;
      array_push($rows, $newVal);
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $rows,
      '#empty' => t('No entries available.'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete all'),
      '#description' => $this->t('Submit, #type = submit'),
    ];
    $form['#attached']['library'][] = 'anzy/my-admin-lib';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form['table']['#value'];
    $connection = \Drupal::service('database');
    /*
     * Cycle through all selected checkboxes,
     * to delete db entry for each one of them.
     */
    foreach ($value as $key => $val) {
      $result = $connection->delete('anzy');
      $result->condition('id', $form['table']['#options'][$key][9]["#value"]);
      $result->execute();
    }
    \Drupal::messenger()->addMessage($this->t('Comments deleted Successfully'), 'status', TRUE);
  }

}
