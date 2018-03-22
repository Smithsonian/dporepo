<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

use PDO;

class RepoStorageHybrid implements RepoStorage {

  private $connection;

  public function __construct($connection) {
    $this->connection = $connection;
  }

  public function getProject($params) {
    //$params will be something like array('project_id' => '123');

    $query_params = array(
      'fields' => array(),
      'base_table' => 'project',
      'search_params' => array(
        0 => array('field_names' => array('project.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('project.project_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'project_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'stakeholder_guid',
    );
    $query_params['fields'][] = array(
      'field_name' => 'project_description',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'last_modified_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'isni_data',
      'field_name' => 'isni_label',
      'field_alias' => 'stakeholder_label',
    );
    $query_params['fields'][] = array(
      'table_name' => 'unit_stakeholder',
      'field_name' => 'unit_stakeholder_repository_id',
      'field_alias' => 'stakeholder_si_guid',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'isni_data',
      'table_join_field' => 'isni_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'project',
      'base_join_field' => 'stakeholder_guid',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'unit_stakeholder',
      'table_join_field' => 'isni_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'project',
      'base_join_field' => 'stakeholder_guid',
    );

    $query_params['records_values'] = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;

  }

  public function getSubject($params) {
    //$params will be something like array('subject_id' => '123');

    $query_params = array(
      'fields' => array(),
      'base_table' => 'subject',
      'search_params' => array(
        0 => array('field_names' => array('subject.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('subject.subject_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND'
    );

    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getRecordById($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(NULL == $record_type || NULL == $record_id) {
      return array();
    }

    $data = $this->getRecord(array(
      'base_table' => $record_type,
      'id_field' => $record_type . '_repository_id',
      'id_value' => $record_id));
    return $data;
  }

  public function deleteRecordById($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(NULL == $record_type || NULL == $record_id) {
      return array();
    }

    $data = $this->deleteRecords(array(
      'base_table' => $record_type,
      'search_params' => array(
        'field_names' => array($record_type . '_repository_id'),
        'search_values' => array($record_id)
        ),
      )
    );
    return $data;
  }

  public function datatablesQuery($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    //@todo- allow match on ID- specify ID field and value $record_match = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;


    $query_params = array(
      'distinct' => true, // @todo Do we always want this to be true?
      'base_table' => $record_type,
      'fields' => array(),
    );

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => $record_type . '.last_modified',
        'sort_order' => 'DESC',
      );
    }


    switch($record_type) {
      case 'subject':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
        );
        $query_params['fields'][] = array(
          'field_name' => 'holding_entity_guid',
        );
        $query_params['fields'][] = array(
          'field_name' => 'local_subject_id',
        );
        $query_params['fields'][] = array(
          'field_name' => 'subject_guid',
        );
        $query_params['fields'][] = array(
          'field_name' => 'subject_name',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );

        $query_params['search_params'][0] = array('field_names' => array('subject.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_type'] = 'AND';
          $query_params['search_params'][1] = array(
            'field_names' => array(
              'subject.subject_name',
              'subject.holding_entity_guid',
              'subject.last_modified'
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }

        // GROUP BY subjects.holding_entity_guid, subjects.local_subject_id, subjects.subject_guid, subjects.subject_name, subjects.last_modified, subjects.active, subjects.subject_repository_id
        $query_params['related_tables'][] = array(
          'table_name' => 'item',
          'table_join_field' => 'subject_repository_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'subject',
          'base_join_field' => 'subject_repository_id',
        );
        break;

      case 'processing_action':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'preceding_processing_action_repository_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'date_of_action',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'action_method',
        );
        $query_params['fields'][] = array(
          'field_name' => 'software_used',
        );
        $query_params['fields'][] = array(
          'field_name' => 'action_description',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array('processing_action.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_type'] = 'AND';
          $query_params['search_params'][1] = array(
            'field_names' => array(
              'processing_action.action_method',
              'processing_action.action_description',
              'processing_action.software_used',
              'processing_action.last_modified'
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        break;

      case 'photogrammetry_scale_bar_target_pair':

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_pair_1_of_2',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_pair_2_of_2',
        );
        $query_params['fields'][] = array(
          'field_name' => 'distance',
        );
        $query_params['fields'][] = array(
          'field_name' => 'units',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array('processing_action.active'), 'search_values' => array(1), 'comparison' => '=');
        break;

    }

  }

  /**
   * ---------------------------------------------------------------
   * Generic functions that get called by other getters and setters.
   * ---------------------------------------------------------------
   */

  public function getRecord($parameters) {

    //@todo confirm params exist
    $base_table = $parameters['base_table'];
    $id_field = $parameters['id_field'];
    $id_value = $parameters['id_value'];

    $query_params = array(
      'fields' => array(),
      'base_table' => $base_table,
      'search_params' => array(
        0 => array('field_names' => array('active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array($id_field), 'search_values' => array($id_value), 'comparison' => '=')
      ),
      'search_type' => 'AND'
    );

    $return_data = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getRecords(array $query_parameters) {

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * fields - array of field_name, field_alias, field_table
     *    if fields array is empty, query will return all fields for base_table
     * related_tables - optional array of table,
     *    where each table is an array of
     *    table_name, table_alias, join_type, table_join_field, base_join_table, base_join_field
     * sort_fields - array containing fields, where each field value is an array of field_name and optionally sort_order
     * limit - array containing limit_start, limit_stop
     * search_params - array containing search values.
     *          Each array value is an array containing field_names, search_values.
     *          field_names and search_values are also arrays.
     *          This makes it possible to structure OR queries or test fields for multiple values
     *          as well as doing a simple 1-to-1 search.
     */

    $base_table = NULL;
    $search_params = array();
    $limit_start = $limit_stop = NULL;
    $select_sql = $join_sql = $search_sql = $sort_sql = $limit_sql = '';
    $data = array();

    // We need certain values: fields, base table. Fail if those aren't provided.
    if(!array_key_exists('fields', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }
    if(!isset($query_parameters['fields']) || !is_array($query_parameters['fields'])) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Fields
    $select_fields_array = array();
    foreach($query_parameters['fields'] as $k => $field) {
      if(array_key_exists('field_name', $field)) {
        $this_field = '';
        if(array_key_exists('table_name', $field)) {
          $this_field = $field['table_name'] . '.';
        }
        $this_field .= $field['field_name'];
        if(array_key_exists('field_alias', $field)) {
          $this_field .= ' as ' . $field['field_alias'];
        }
        $select_fields_array[] = $this_field;
      }
    }
    if(count($select_fields_array) < 1) {
      $select_fields_array[] = $base_table . '.*';
    }
    $select_sql = implode(', ', $select_fields_array);
    if(array_key_exists('distinct', $query_parameters)) {
      $select_sql = ' DISTINCT ' . $select_sql;
    }

    if(array_key_exists('related_tables', $query_parameters) && is_array($query_parameters['related_tables'])
      && count($query_parameters['related_tables']) > 0) {
      $joins = array();
      foreach($query_parameters['related_tables'] as $rt) {
        if(!array_key_exists('table_name', $rt) || !array_key_exists('join_type', $rt)
          || !array_key_exists('base_join_table', $rt) || !array_key_exists('base_join_field', $rt)
          || !array_key_exists('table_join_field', $rt)
        ) {
          continue;
        }

        if(strtoupper($rt['join_type']) !== 'LEFT JOIN' && strtoupper($rt['join_type']) !== 'INNER JOIN'
          && strtoupper($rt['join_type']) !== 'OUTER JOIN' && strtoupper($rt['join_type']) !== 'JOIN'
        ) {
          continue;
        }
        $join = ' ' . $rt['join_type'] . ' ' . $rt['table_name'];
        if(array_key_exists('table_alias', $rt)) {
          $join .= ' ' . $rt['table_alias'];
        }
        $join .= ' ON ' . $rt['base_join_table'] . '.' . $rt['base_join_field'] . ' = ' . $rt['table_name'] . '.' . $rt['table_join_field'];

        $joins[] = $join;
      }
      if(count($joins) > 0) {
        $join_sql = implode(' ', $joins);
      }

    }

    // Limit
    if(array_key_exists('limit', $query_parameters) && is_array($query_parameters['limit'])) {
      $limit = $query_parameters['limit'];
      //@todo other checks? Like > 0 ?
      if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
        $limit_start = $limit['limit_start'];
        if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
          $limit_stop = $limit['limit_stop'];
        }
        if(NULL !== $limit_stop) {
          $limit_sql = " LIMIT {$limit_start}, {$limit_stop} ";
        }
        else {
          $limit_sql = " LIMIT {$limit_start} ";
        }
      }
    }

    // Sort
    if(array_key_exists('sort_fields', $query_parameters) && is_array($query_parameters['sort_fields'])) {
      $sort_params = array();
      foreach($query_parameters['sort_fields'] as $fld) {
        $s = $fld['field_name'];
        if(array_key_exists('sort_order', $fld) && $fld['sort_order'] == 'DESC') {
          $s .= ' ' . $fld['sort_order'];
        }
        $sort_params[] = $s;
      }
      $sort_sql = implode(', ', $sort_params);
      if(strlen(trim($sort_sql)) > 0) {
        $sort_sql = " ORDER BY " . $sort_sql;
      }
    }

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if((!is_array($search_values) && strlen(trim($search_values)) > 0)) {
          $search_values = array($search_values);
        }
        if(!is_array($field_names) || count($field_names) == 0 || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            if(array_key_exists('comparison', $p) && $p['comparison'] !== 'LIKE') {
              $this_search_param[] = $fn . ' ' . $p['comparison'] . ' ?';
              $k = array_keys($search_values)[0];
              $search_params[] = $search_values[$k];
            }
            else {
              $this_search_param[] = $fn . ' LIKE ?';
              $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
            }
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values[] = '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        if(array_key_exists('search_type', $query_parameters) && $query_parameters['search_type'] != 'OR') {
          $search_sql = implode(' ' . $query_parameters['search_type'] . ' ', $search_sql_values);
        }
        else {
          $search_sql = implode(' OR ', $search_sql_values);
        }
      }
    }

    $sql = "SELECT " . $select_sql .
      " FROM " . $base_table;

    if(strlen($join_sql) > 0) {
      $sql .= $join_sql;
    }

    if(strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }
    if(strlen(trim($sort_sql)) > 0) {
      $sql .= " {$sort_sql} ";
    }
    if(strlen(trim($limit_sql)) > 0) {
      $sql .= $limit_sql;
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $records_values = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $records_values;

  }

  /***
   * @param $query_parameters parameters used to query records for return.
   * Sets records into $records_values.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function getRecordsDatatable(array $query_parameters) {

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * fields - array of field_name, field_alias, field_table
     *    if fields array is empty, query will return all fields for base_table
     * related_tables - optional array of table,
     *    where each table is an array of
     *    table_name, table_alias, join_type, table_join_field, base_join_table, base_join_field
     * sort_fields - array containing fields, where each field value is an array of field_name and optionally sort_order
     * limit - array containing limit_start, limit_stop
     * search_params - array containing search values.
     *          Each array value is an array containing field_names, search_values.
     *          field_names and search_values are also arrays.
     *          This makes it possible to structure OR queries or test fields for multiple values
     *          as well as doing a simple 1-to-1 search.
     */

    $base_table = NULL;
    $search_params = array();
    $limit_start = $limit_stop = NULL;
    $select_sql = $join_sql = $search_sql = $sort_sql = $limit_sql = '';
    $data = array();

    // We need certain values: fields, base table. Fail if those aren't provided.
    if(!array_key_exists('fields', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }
    if(!isset($query_parameters['fields']) || !is_array($query_parameters['fields'])) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Fields
    $select_fields_array = array();
    foreach($query_parameters['fields'] as $k => $field) {
      if(array_key_exists('field_name', $field)) {
        $this_field = '';
        if(array_key_exists('table_name', $field)) {
          $this_field = $field['table_name'] . '.';
        }
        $this_field .= $field['field_name'];
        if(array_key_exists('field_alias', $field)) {
          $this_field .= ' as ' . $field['field_alias'];
        }
        $select_fields_array[] = $this_field;
      }
    }
    if(count($select_fields_array) < 1) {
      $select_fields_array[] = $base_table . '.*';
    }
    $select_sql = implode(', ', $select_fields_array);
    if(array_key_exists('distinct', $query_parameters)) {
      $select_sql = ' DISTINCT ' . $select_sql;
    }

    if(array_key_exists('related_tables', $query_parameters) && is_array($query_parameters['related_tables'])
    && count($query_parameters['related_tables']) > 0) {
      $joins = array();
      foreach($query_parameters['related_tables'] as $rt) {
        if(!array_key_exists('table_name', $rt) || !array_key_exists('join_type', $rt)
          || !array_key_exists('base_join_table', $rt) || !array_key_exists('base_join_field', $rt)
          || !array_key_exists('table_join_field', $rt)
        ) {
          continue;
        }

        if(strtoupper($rt['join_type']) !== 'LEFT JOIN' && strtoupper($rt['join_type']) !== 'INNER JOIN'
          && strtoupper($rt['join_type']) !== 'OUTER JOIN' && strtoupper($rt['join_type']) !== 'JOIN'
        ) {
          continue;
        }
        $join = ' ' . $rt['join_type'] . ' ' . $rt['table_name'];
        if(array_key_exists('table_alias', $rt)) {
          $join .= ' ' . $rt['table_alias'];
        }
        $join .= ' ON ' . $rt['base_join_table'] . '.' . $rt['base_join_field'] . ' = ' . $rt['table_name'] . '.' . $rt['table_join_field'];

        $joins[] = $join;
      }
      if(count($joins) > 0) {
        $join_sql = implode(' ', $joins);
      }

    }

    // Limit
    if(array_key_exists('limit', $query_parameters) && is_array($query_parameters['limit'])) {
      $limit = $query_parameters['limit'];
      //@todo other checks? Like > 0 ?
      if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
        $limit_start = $limit['limit_start'];
        if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
          $limit_stop = $limit['limit_stop'];
        }
        if(NULL !== $limit_stop) {
          $limit_sql = " LIMIT {$limit_start}, {$limit_stop} ";
        }
        else {
          $limit_sql = " LIMIT {$limit_start} ";
        }
      }
    }

    // Sort
    if(array_key_exists('sort_fields', $query_parameters) && is_array($query_parameters['sort_fields'])) {
      $sort_params = array();
      foreach($query_parameters['sort_fields'] as $fld) {
        $s = $fld['field_name'];
        if(array_key_exists('sort_order', $fld) && $fld['sort_order'] == 'DESC') {
          $s .= ' ' . $fld['sort_order'];
        }
        $sort_params[] = $s;
      }
      $sort_sql = implode(', ', $sort_params);
      if(strlen(trim($sort_sql)) > 0) {
        $sort_sql = " ORDER BY " . $sort_sql;
      }
    }

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if((!is_array($search_values) && strlen(trim($search_values)) > 0)) {
          $search_values = array($search_values);
        }
        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            if(array_key_exists('comparison', $p) && $p['comparison'] !== 'LIKE') {
              $this_search_param[] = $fn . ' ' . $p['comparison'] . ' ?';
              $k = array_keys($search_values)[0];
              $search_params[] = $search_values[$k];
            }
            else {
              $this_search_param[] = $fn . ' LIKE ?';
              $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
            }
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values[] = '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        if(array_key_exists('search_type', $query_parameters) && $query_parameters['search_type'] != 'OR') {
          $search_sql = implode(' ' . $query_parameters['search_type'] . ' ', $search_sql_values);
        }
        else {
          $search_sql = implode(' OR ', $search_sql_values);
        }
      }
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS "
      . $select_sql .
      " FROM " . $base_table;

    if(strlen($join_sql) > 0) {
      $sql .= $join_sql;
    }

    if(strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }
    if(strlen(trim($sort_sql)) > 0) {
      $sql .= " {$sort_sql} ";
    }
    if(strlen(trim($limit_sql)) > 0) {
      $sql .= $limit_sql;
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

    return $data;

  }

  /***
   * @param null $records_values data and metadata to save.
   * Attempts to save data, and updates $records_values accordingly.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function setRecords(array $query_parameters){

    /*
     * $query_parameters['records_values'] should contain an array of record values.
     *          Each record_value should have field_name, field_value.
     *
     *          $records_values may have an entry with key 'id'.
     *          If the array item key is 'id' and field_value is set, this indicates that the record
     *          already exists, and triggers an UPDATE statement rather than an INSERT.
     *          If the item key is 'id' and field_value is not set, an INSERT will be performed,
     *          and field_value will be updated from the id returned after inserting.
     *
     * $query_parameters should contain the info necessary to determine which records to update:
     * base_table - string indicating base table for update
     * search_params
     */

    $base_table = NULL;
    $data = array();

    // We need certain values: record_values, base table. Fail if those aren't provided.
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }
    if(!isset($query_parameters['records_values']) || !is_array($query_parameters['records_values'])) {
      return array('return' => 'fail', 'messages' => array('No record values specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_string = '';
        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $this_search_param[] = $fn . ' LIKE ?';
            $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values .= '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        $search_sql = implode(' AND ', $search_sql_values);
      }
    }

    foreach($query_parameters['records_values'] as $record_values) {
      $update = false;
      $fields_sql_array = $fields_values_sql_array = $fields_params = array();

      if(array_key_exists('id', $record_values)
        && array_key_exists('field_value', $record_values['id']) && isset($record_values['id']['field_value'])
        && array_key_exists('field_name', $record_values['id']) && isset($record_values['id']['field_name'])
      )
      {
        $update = true;
      }

      foreach($record_values as $rv) {
        if(!array_key_exists('id', $rv)
          && array_key_exists('field_value', $rv) && isset($rv['field_value'])
          && array_key_exists('field_name', $rv) && isset($rv['field_name'])
        ) {
          if($update) {
            $fields_sql_array[] = $rv['field_name'] . " = :" . $rv['field_name'];
          }
          else {
            $fields_sql_array[$rv['field_name']] = ":" . $rv['field_name'];
          }
          $fields_params[":" . $rv['field_name']] = $rv['field_value'];
        }
      }

      if(count($fields_sql_array) > 0 && count($fields_sql_array) == count($fields_params)) {
        if($update) {
          $sql ="UPDATE " . $base_table;
          $sql .= " SET " . implode(',', $fields_sql_array);
          $sql .= " WHERE " . $query_parameters['records_values']['id']['field_name'] . " = :id ";

          $statement = $this->connection->prepare($sql);
          foreach($fields_params as $fn1 => $fv1) {
            $statement->bindValue($fn1, $fv1);
          }
          $statement->bindValue(":id", $query_parameters['records_values']['id']['field_value'], PDO::PARAM_INT);
          $statement->execute();
        }
        else {
          $statement = $this->connection->prepare("INSERT INTO " . $this->table_name . "
                (" . $this->label_field_name_raw . ", date_created, created_by_user_account_id, last_modified_user_account_id)
                VALUES (:" . $this->label_field_name_raw . ", NOW(), :user_account_id, :user_account_id)");
          $statement->bindValue(":" . $this->label_field_name_raw . "", $data[$this->label_field_name_raw], PDO::PARAM_STR);
          $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);


          $sql ="INSERT INTO " . $base_table;
          $sql .= " (" . implode(',', array_keys($fields_sql_array)) . ')';
          $sql .= " VALUES (" . implode(',', array_values($fields_sql_array)) . ')';

          $statement = $this->connection->prepare($sql);
          foreach($fields_params as $fn1 => $fv1) {
            $statement->bindValue($fn1, $fv1);
          }
          $statement->execute();
          $last_inserted_id = $this->connection->lastInsertId();

          if(!$last_inserted_id) {
            //@todo die('INSERT INTO `' . $this->table_name . '` failed.');
          }
        }

      } // if we have enough info to perform an insert or update

    } // each set of record values

    return array('return' => 'success');
  }

  /***
   * @param $query_parameters parameters used to query records for deletion.
   * Attempts to delete specified records.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function deleteRecords(array $query_parameters){

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * search_params - array containing fields and values to use to locate the record for deletion.
     *          Each search_params value is an array containing field_names, search_values.
     */

    $base_table = NULL;
    $search_sql = '';
    $data = array();

    // We need base table. Fail if that isn't provided.
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }

    if(isset($query_parameters['search_params']) && !is_array($query_parameters['search_params'])) {
      return array('return' => 'fail', 'messages' => array('Fields parameter is invalid.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $this_search_param[] = $fn . ' LIKE ?';
            $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values .= '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        $search_sql = implode(' AND ', $search_sql_values);
      }
    }

    if(true == $query_parameters['delete_children']) {
      //@todo delete children first

      switch($base_table) {
        case 'project':
          break;
        case 'subject':
          break;
        case 'item':
          break;

      }
    }

    $sql = "DELETE FROM " . $base_table;

    if(strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success', 'data' => $return);
  }

  /***
   * @param $query_parameters parameters used to query records to be marked inactive.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function markRecordsInactive(array $query_parameters){

    /*
     * $query_parameters should contain:
     * ------------
     * record_type - string indicating base table for query
     * record_id
     * user_id
     */

    $record_type = NULL;
    $search_sql = '';
    $search_params = array();
    $data = array();

    // We need base table. Fail if that isn't provided.
    if(!array_key_exists('record_type', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No record_type parameter specified.'));
    }
    if(!array_key_exists('user_id', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No user_id parameter specified.'));
    }
    if(isset($query_parameters['search_params']) && !is_array($query_parameters['search_params'])) {
      return array('return' => 'fail', 'messages' => array('Fields parameter is invalid.'));
    }

    // Table
    $record_type = $query_parameters['record_type'];

    $search_params[] = $query_parameters['user_id'];

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $this_search_param[] = $fn . ' LIKE ?';
            $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values .= '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        $search_sql = implode(' AND ', $search_sql_values);
      }
    }

    if(true == $query_parameters['delete_children']) {
      //@todo delete children first

      switch($record_type) {
        case 'project':
          break;
        case 'subject':
          break;
        case 'item':
          break;

      }
    }

    $sql = "UPDATE " . $record_type . " SET active = 0, last_modified_user_account_id=:";

    if(strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success', 'data' => $return);
  }

}