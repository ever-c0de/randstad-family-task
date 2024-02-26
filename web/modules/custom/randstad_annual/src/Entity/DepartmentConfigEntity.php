<?php

namespace Drupal\randstad_annual\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Department configuration entity.
 *
 * @ConfigEntityType(
 *   id = "randstad_annual_department",
 *   label = @Translation("Department"),
 *   config_prefix = "department",
 *   handlers = {
 *     "list_builder" = "Drupal\randstad_annual\Entity\DepartmentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\randstad_annual\Form\DepartmentConfigEntityAdd",
 *     }
 *   },
 *   admin_permission = "manage event registrations",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *      "id",
 *      "label",
 *    },
 *   links = {
 *      "create" = "/admin/config/add-department",
 *      "collection" = "/admin/config/departments",
 *    }
 * )
 */
class DepartmentConfigEntity extends ConfigEntityBase {

  /**
   * The Department config id.
   *
   * @var string
   */
  protected $id;

  /**
   * The Department config label.
   *
   * @var string
   */
  protected $label;

}
