<?php

namespace Drupal\simplified_bookkeeping\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class DefaultForm.
 */
class ConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('simplified_bookkeeping.settings');

    $form['membership_payment_received_email'] = array(
      '#title' => t('Send membership payment email'),
      '#type' => 'checkbox',
      '#description' => t('Should we send an email when a membership payment was received and processed?'),
      '#default_value' => $config->get('membership_payment_received_email'),
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('simplified_bookkeeping.settings')
      ->set('membership_payment_received_email', $form_state->getValue('membership_payment_received_email'))
      ->save();
  }
}
