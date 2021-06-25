<?php

namespace Drupal\anzy\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Contains \Drupal\anzy\Form\gbookDeleteForm.
 *
 * @file
 */

/**
 * Provides an gbook delete form.
 */
class GbookDeleteForm extends FormBase {
  /**
   * Contain slug id to delete review entry.
   *
   * @var ctid
   */
  protected $ctid = 0;

  /**
   * Contain slug number to redirect review entry.
   *
   * @var rpathh
   */
  protected $rpathh = 0;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gbook_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL, $rpath = NULL) {
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#ajax' => [
        'callback' => '::ajaxForm',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $this->ctid = $cid;
    $this->rpathh = $rpath;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    $result = $connection->delete('anzy');
    $result->condition('id', $this->ctid);
    $result->execute();
    \Drupal::messenger()->addMessage($this->t('Entry deleted successfully'), 'status', TRUE);
  }

  /**
   * Function to reload page.
   */
  public function ajaxForm(array &$form, FormStateInterface $form_state) {
    if ($this->rpathh == 10) {
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand('/anzy/gbook'));
    }
    elseif ($this->rpathh == 20) {
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand('/admin/structure/gbook-comments'));
    }
    return $response;
  }

}
