<?php

namespace Drupal\anzy\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\file\Entity\File;

/**
 * Contains \Drupal\anzy\Form\gbookChangeForm.
 *
 * @file
 */

/**
 * Provides an gbook edit form.
 */
class GbookChangeForm extends FormBase {

  /**
   * Contain slug id to edit review entry.
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
    return 'gbook_change_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL, $rpath = NULL) {
    $form['system_messages'] = [
      '#markup' => '<div id="message-error" class="messages messages--error"></div>',
      '#weight' => -100,
    ];
    $form['name'] = [
      '#title' => t("Your name:"),
      '#type' => 'textfield',
      '#size' => 32,
      '#description' => t("Name should be at least 2 characters and less than 32 characters"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateAjax',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying name..'),
        ],
      ],
    ];
    $form['phone'] = [
      '#title' => t("Your phone number:"),
      '#type' => 'textfield',
      '#size' => 15,
      '#required' => TRUE,
      '#description' => t("Your phone number should be standard format as for any country example +10990000000"),
      '#ajax' => [
        'callback' => '::validateAjax',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying name..'),
        ],
      ],
    ];
    $form['email'] = [
      '#title' => t("Email:"),
      '#type' => 'email',
      '#description' => t("example@gmail.com"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateAjax',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying email..'),
        ],
      ],
    ];
    $form['image'] = [
      '#title' => t("Review image:"),
      '#type' => 'managed_file',
      '#upload_location' => 'public://module-images',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5297152],
      ],
      '#description' => t("insert image below size of 2MB. Supported formats: png jpg jpeg."),
    ];
    $form['avatar'] = [
      '#title' => t("Your photo:"),
      '#type' => 'managed_file',
      '#upload_location' => 'public://module-images',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#description' => t("insert image below size of 2MB. Supported formats: png jpg jpeg."),
      '#default_value' => [66],
    ];
    $form['comment'] = [
      '#title' => t("Your review:"),
      '#type' => 'textarea',
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add review'),
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', t('The name is too short. Please enter valid name.'));
    }
    elseif (strlen($form_state->getValue('name')) > 100) {
      $form_state->setErrorByName('name', t('The name is too long. Please enter valid name.'));
    }
    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', t('Invalid email format. Please enter valid email.'));
    }
    elseif (preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\0-9]/', $form_state->getValue('email'))) {
      $form_state->setErrorByName('email', t('The email should match example. Please enter valid email.'));
    }
    if (strlen($form_state->getValue('phone')) < 9) {
      $form_state->setErrorByName('phone', t('The phone number is too short. Please enter valid phone number.'));
    }
    elseif (strlen($form_state->getValue('phone')) > 15) {
      $form_state->setErrorByName('phone', t('The phone number is too long. Please enter valid phone number.'));
    }
    elseif (preg_match('/^[0-9\-\(\)\/\+\s]*$/', $form_state->getValue('email'))) {
      $form_state->setErrorByName('phone', t('The phone number should contain only numbers. Please enter valid phone number.'));
    }
  }

  /**
   * Function that validate email input with ajax.
   */
  public function validateAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (strlen($form_state->getValue('name')) < 2) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The name is too short. Please enter valid name.') . '</div>'));
    }
    elseif (strlen($form_state->getValue('name')) > 100) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The name is too long. Please enter valid name.') . '</div>'));
    }
    elseif (preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\0-9]/', $form_state->getValue('email'))) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The email should match example. Please enter valid email.') . '</div>'));
    }
    elseif (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('Invalid email format. Please enter valid email.') . '</div>'));
    }
    elseif (strlen($form_state->getValue('phone')) < 2) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The phone number is too short. Please enter valid phone number.') . '</div>'));
    }
    elseif (strlen($form_state->getValue('phone')) > 15) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The phone number is too long. Please enter valid phone number.') . '</div>'));
    }
    elseif (!preg_match('/^[0-9\-\(\)\/\+\s]*$/', $form_state->getValue('phone'))) {
      $response->addCommand(new HtmlCommand('#message-error', '<div class="alert alert-dismissible fade show col-12 alert-danger">' . t('The phone number should contain only number. Please enter valid phone number.') . '</div>'));
    }
    else {
      $response->addCommand(new HtmlCommand('#message-error', ''));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    if (!$form_state->getValue('image')[0] == NULL) {
      $file = File::load($form_state->getValue('image')[0]);
      $file->setPermanent();
      $file->save();
    }
    else {
      $form_state->getValue('image')[0] = 0;
    }
    if (!$form_state->getValue('avatar')[0] == NULL) {
      $ava = File::load($form_state->getValue('avatar')[0]);
      $ava->setPermanent();
      $ava->save();
    }
    else {
      $form_state->getValue('avatar')[0] = 0;
    }
    $result = $connection->update('anzy')
      ->condition('id', $this->ctid)
      ->fields([
        'name' => $form_state->getValue('name'),
        'comment' => $form_state->getValue('comment'),
        'phone' => $form_state->getValue('phone'),
        'avatar' => $form_state->getValue('avatar')[0],
        'mail' => $form_state->getValue('email'),
        'image' => $form_state->getValue('image')[0],
      ])
      ->execute();
    \Drupal::messenger()->addMessage($this->t('Form Edit Successfully'), 'status', TRUE);
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
