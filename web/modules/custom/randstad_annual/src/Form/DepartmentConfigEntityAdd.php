<?php

namespace Drupal\randstad_annual\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * DepartmentConfigEntity Add form.
 */
class DepartmentConfigEntityAdd extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $department = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $department->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#required' => TRUE,
      '#disabled' => !$this->entity->isNew(),
      '#size' => 30,
      '#maxlength' => 64,
      '#machine_name' => [
        'exists' => ['\Drupal\randstad_annual\Entity\DepartmentConfigEntity', 'load'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $department = $this->entity;
    $status = $department->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label Department created.', [
        '%label' => $department->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Department updated.', [
        '%label' => $department->label(),
      ]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

}
