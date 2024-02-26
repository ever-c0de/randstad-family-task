<?php

namespace Drupal\randstad_annual\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an event registration form.
 */
class RandstadAnnualRegisterForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $nodeStorage;

  /**
   * The email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * Constructs a new RandstadAnnualRegisterForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *    The email validator service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EmailValidatorInterface $email_validator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'randstad_annual_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Registration form fields.
    $form['field_employee_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the employee'),
      '#description' => $this->t("Please, type your name."),
      '#maxlength' => 255,
      '#required' => TRUE,
    ];

    $form['field_one_plus'] = [
      '#type' => 'radios',
      '#title' => $this->t('One plus'),
      '#description' => $this->t("Please, check this if you want to bring someone."),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => 0,
      '#required' => TRUE,
    ];

    $form['field_kids_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of kids'),
      '#description' => $this->t("How many kids is going."),
      '#default_value' => 0,
      '#min' => 0,
      '#max' => 20,
      '#required' => TRUE,
    ];

    $form['field_vegetarians_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of vegetarians'),
      '#description' => $this->t("How many vegetarians is going."),
      '#default_value' => 0,
      '#min' => 0,
      '#max' => 22,
      '#required' => TRUE,
    ];

    $form['field_email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#description' => $this->t("Please, type your email address."),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Checks if number of vegetarians is not higher than
    // the total amount people (1 – it is registering employee).
    $total_people = $values['field_kids_amount'] + $values['field_one_plus'] + 1;
    if ($values['field_vegetarians_amount'] > ($total_people)) {
      $form_state->setErrorByName('field_vegetarians_amount', $this->t('The number of vegetarians - %vegetarians is higher than number of people - @total.', [
        '%vegetarians' => $values['field_vegetarians_amount'],
        '@total' => $total_people,
      ]));
    }

    // Checks if email is valid.
    if (!$this->emailValidator->isValid($values['field_email_address'])) {
      $form_state->setErrorByName('field_email_address', $this->t('%email is an invalid email address.', ['%email' => $values['field_email_address']]));
    }

    // Checks if employee is not registered yet. Should execute last for resource optimization.
    if ($this->nodeStorage->loadByProperties([
      'type' => 'registration',
      'field_email_address' => $values['field_email_address'],
    ])) {
      $form_state->setErrorByName('field_email_address', $this->t("Sorry, the email address - %address already registered for annual event.", [
        '%address' => $values['employee_email'],
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $secure_name = Xss::filter($values['field_employee_name']);

    try {
      $this->nodeStorage->create([
        'type' => 'registration',
        'title' => $secure_name,
        'field_employee_name' => $secure_name,
        'field_one_plus' => $values['field_one_plus'],
        'field_kids_amount' => $values['field_kids_amount'],
        'field_vegetarians_amount' => $values['field_vegetarians_amount'],
        'field_email_address' => $values['field_email_address'],
      ])->save();
      $this->messenger()
        ->addStatus($this->t("Registered for event successfully!"));
    }
    catch (\Exception $e) {
      $this->messenger()
        ->addError($this->t("Sorry, seems that 'Registration' content type is not created!"));
    }
  }

}
