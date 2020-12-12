<?php

namespace Drupal\example_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Ajax form for users.
 */
class Users extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_users';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['identification'] = [
      '#type' => 'number',
      '#title' => $this->t('Identification'),
      '#required' => TRUE,
    ];

    $form['birthdate'] = [
      '#type' => 'date',
      '#title' => $this->t('Birth Date'),
      '#description' => $this->t('Date of birth.'),
      '#required' => TRUE,
    ];

    $form['birthdate'] = [
      '#type' => 'date',
      '#title' => $this->t('Birth Date'),
      '#description' => $this->t('Date of birth.'),
    ];

    $form['position'] = [
      "#type" => "select",
      "#title" => $this->t("Position"),
      "#default_value" => 'administrator',
      "#options" => [
        "administrator" => $this->t("Administrator"),
        "webmaster" => $this->t("Webmaster"),
        "desarrollador" => $this->t("Developer"),
      ],
    ];

    $form['cancel'] = [
      '#type' => 'html_tag',
      '#tag' => 'input',
      '#attributes' => [
        'type' => 'button',
        'value' => $this->t("Cancel"),
        'class' => 'button',
        'name' => 'cancel',
        'onclick' => "window.location='/example-module/data'",
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#ajax' => [
        'callback' => '::submitForm',
      ],
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    if (!empty($name) and !ctype_alnum($name)) {
      \Drupal::messenger()->addMessage($this->t('The field name not allow special characters. Just is possible characters alphanumeric.'), 'error', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messages = drupal_get_messages();
    if (empty($messages)) {
      $fields = [
        'name' => $form_state->getValue('name'),
        'identification' => $form_state->getValue('identification'),
        'birthdate' => strtotime($form_state->getValue('birthdate')),
        'position' => $form_state->getValue('position'),
        'state' => ($form_state->getValue('position') == 'administrator') ? 1 : 0,
      ];
      try {
        $db_connection = \Drupal::database();
        $result = $db_connection->insert('example_users')
          ->fields($fields)
          ->execute();
        if ($result) {
          \Drupal::messenger()->addMessage($this->t('User @name created succesfully.', ['@name' => ($form_state->getValue('name'))]), 'status', TRUE);
        }
        else {
          \Drupal::messenger()->addMessage($this->t('Problem with the database, please try again.'), 'error', TRUE);
        }
      }
      catch (\Exception $e) {
        \Drupal::messenger()->addMessage($this->t('A problem report in the watchdog.'), 'error', TRUE);
        // Log the exception to watchdog.
        \Drupal::logger('example_users')->error($e->getMessage());
      }
    }
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $messages,
    ];

    $messages = \Drupal::service('renderer')->render($message);
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.result_message', $messages));
    return $response;
  }

}
