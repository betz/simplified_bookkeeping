<?php

namespace Drupal\simplified_bookkeeping\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigratePluginManagerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Uses the str_replace() method on a source string.
 *
 * @MigrateProcessPlugin(
 *   id = "str_replace"
 * )
 *
 * To do a simple hardcoded string replace use the following:
 *
 * @code
 *   field_text:
 *     plugin: str_replace
 *     source: text
 *     search: foo
 *     replace: bar
 * @endcode
 *
 * This example would replace any instance of 'foo' with 'bar' in the values
 * migrated in via the source field 'text'
 *
 * @code
 *   field_text:
 *     plugin: str_replace
 *     source: text
 *     replace: bar
 * @endcode
 *
 * The above will replace what ever is in the source field 'search_field' with
 * the string 'bar'.
 *
 * You can provide a source field for replacing as well. Using the
 * 'replace_source' configuration.
 * @code
 *   field_text:
 *     plugin: str_replace
 *     source: text
 *     search: "<author>"
 * @endcode
 *
 * You can use both search_source and replace_source at the same time. However,
 * the Search and Replace configurations will override what ever is provided in
 * search_source or replace_source if all configurations are provided.
 *
 * Both source fields use the
 * @link Drupal\migrate\Plugin\migrate\process\get get @endlink process plugin
 * so the use of destination fields are possible like so:
 * @code
 *   version_api_search_source:
 *     plugin: concat
 *     source:
 *       - api
 *       - constants/dash
 *   field_project_version:
 *     plugin: str_replace
 *     source: version
 *     replace: ""
 * @endcode
 *
 * Case insensitive searches can be achieved using the following:
 * @code
 *   field_text:
 *     plugin: str_replace
 *     case_insensitive: true
 *     source: text
 *     search: foo
 *     replace: bar
 * @endcode
 *
 * All the rules for
 * @link http://php.net/manual/function.str-replace.php str_replace @endlink
 * apply. This means that you can provide arrays as values.
 */
class StrReplace extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * The migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * The Migration Process Plugin Manager.
   *
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $processPluginManager;

  /**
   * Constructs a StrReplace plugin.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $pluginId
   *   The plugin identifier.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration.
   * @param Drupal\migrate\Plugin\MigratePluginManagerInterface $process_plugin_manager
   *   The process plugin manager.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration, MigratePluginManagerInterface $process_plugin_manager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->migration = $migration;
    $this->processPluginManager = $process_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $migration,
      $container->get('plugin.manager.migrate.process')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
   if (!isset($this->configuration['search'])) {
     throw new MigrateException('"search" must be configured.');
   }
    if (!isset($this->configuration['replace'])) {
      throw new MigrateException('"replace" must be configured.');
    }
    $this->multiple = is_array($value);
    $function = "str_replace";
    if ($this->configuration['case_insensitive']) {
      $function = 'str_ireplace';
    }
    return $function($this->configuration['search'], $this->configuration['replace'], $value);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }
}
