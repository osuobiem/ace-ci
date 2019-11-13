<?php

/**
 * Ace_Model Class
 * 
 * Database model abstraction layer to interact with
 * CodeIgniter core model functions, and also
 * to reduce the weight of child models.
 * 
 * @package Ace-CI
 * @version 1.0
 * @author  Gabriel Osuobiem <osuobiem@gmail.com>
 * @link https://github.com/osuobiem
 * @link https://www.linkedin.com/in/gabriel-osuobiem-b22577176/
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Ace_Model extends CI_Model {

  // Table name (To be specified in child classes)
  protected $table;

  // Entity relationship declaration array
  protected $relation;

  // Preserved column value of a fetched record
  public $preserved;

  /**
   * Relationship Types
   * 
   * Variable that specifies the current
   * relationship the current entity has
   * with another entity
   * 
   * $rtype can be any of the following:
   *    'm-m' --> Many-to-Many
   *    '1-m' --> One-to-Many
   *    'm-1' --> Many-to-One
   *    '1-1' --> One-to-One
   */
  public $rtype;

  public function __construct() {
    parent::__construct();
    
    $this->load->database();
  }

  /**
   * Create Record
   * 
   * Create new database record
   * 
   * @param array $data   Associative array of data
   * @param bool (optional) $get_id  Boolean value that
   *                        specifies if the insert id should
   *                        be returned after insertion.
   * 
   * @return string|bool   Integer of last insert id (if requested for)
   *                        or true|false
   */
  public function create($data, $get_id = false){
		if($get_id) {
      $res = $this->db->insert($this->table, $data) ? $this->db->insert_id() : false;
    }
    else {
      $res = $this->db->insert($this->table, $data) ? true : false;
    }

    return $res;
	}

  /**
   * Create Batch Records
   * 
   * Create new database records in batches
   * 
   * @param array $data   Associative array of data
   * 
   * @return string|bool   String of insert id (if requested for)
   *                        or true|false
   */
  public function createBatch($data){
		$res = $this->db->insert_batch($this->table, $data) ? true : false;

    return $res;
  }
  
  /**
   * Build query
   * 
   * Build mysql query with correspondence to specified options
   * 
   * @param array $options   Associative array of query specifications
   * 
   * @return null
   */
  public function build($options) {
    if(!empty($options)) {
      foreach($options as $key => $option) {

        switch ($key) {
          case 'order_by':
            $opt_key = key($option);
            $this->db->order_by($opt_key, $option[$opt_key]);
            break;
          
          case 'distinct':
            $this->db->select($options['distinct']);
            $this->db->distinct();
            break;

          case 'or_where':
            if(array_keys($option) === range(0, count($option) - 1)) {
              foreach($option as $op) {
                $this->db->or_where($op);
              }
            }
            else {
              $this->db->or_where($option);
            }
            break;

          case 'like':
            $opt_key = key($option);
            $this->db->like($opt_key, $option[$opt_key]);
            break;
          
          default:
            $this->db->{$key}($option);
            break;
        }

        next($options);
      }
    }
    else {
      return;
    }
  }

  /**
   * Get Records
   * 
   * Retrieve records from the database
   * 
   * @param array (oprional) $options   Associative array of filters
   *  $options has the form --> ['name_of_filter' => array_of_values]
   * 
   *  Example; $options = ['where'=>['id' => 1, 'name' => 'Gabriel'],
   *                      'order_by'=>['name' => 'ASC']]
   *  Builds into 
   *    "SELECT * FROM $table WHERE id = 1 && name = 'Gabriel' ORDER BY name ASC"
   * 
   * @return array Array of database record objects
   */
  public function get($options = []) {
    $this->build($options);

		return $this->db->get($this->table)->result();
  }

  /**
   * Get One Record
   * 
   * Retrieve a single record from the database
   * 
   * @param array $option Associative array of filters
   *  $option has the form --> ['id' => 1]
   * @param string (optional) $preserve  String that specifies
   *                                  the column value should
   *                                  be preserved.
   * 
   * @return object Object of a single database record
   */
  public function getOne($option, $preserve = false) {
    $this->db->where($option);
    $result = $this->db->get($this->table)->row();

    if($preserve) {
      if(count((array) $result) > 0) {
        $this->preserved = $result->{$preserve};
      }
    }

    return $result;
  }

  /**
   * Get Records Count
   * 
   * Get the number of retrieved records
   * 
   * @param array (optional) $option Associative array of filters
   *  $option has the form --> ['id' => 1]
   * 
   * @return int  Number of records retreived
   */
  public function getCount($option = []) {
    if(count($option) > 0) {
      $build = true;
      foreach($option as $key) {
        if(gettype($key) != 'array') {
          $build = false;
        }
      }
      if($build) {
        $this->build($option);
      }
      else {
        $this->db->where($option);
      }
    }
    return $this->db->count_all_results($this->table);
  }
  
  /**
   * Update Record(s)
   * 
   * Update database record(s)
   * 
   * @param array $options  Associative array of filters
   *  $options has the form --> ['filter'=>['name_of_filter' => array_of_values],
   *                            'data' => array_of_field_values]
   * 
   *  Example; $options = ['filter'=>['id' => 1],
   *                      'data'=>['name' => 'Gabriel']]
   *  Builds into 
   *    "UPDATE $table SET name = 'Gabriel' WHERE id = 1"
   * 
   * @return bool True if successful and False if otherwise
   */
  public function update($options){
		$this->db->where($options['filter']);
		return $this->db->update($this->table, $options['data']) ? true : false;
	}

  /**
   * Delete Record(s)
   * 
   * Delete database record(s)
   * 
   * @param array $option Associative array of filters
   *  $option has the form --> ['id' => 1]
   * 
   * @param bool (optional) $one  Check if there's only one record left
   * 
   * @return bool True if successful and False if otherwise
   */
  public function delete($option, $one = false){
    if($one) {
      if($this->getCount() == 1) {
        return 'Only one record left';
      } 
    }

		$this->db->where($option);
		return $this->db->delete($this->table) ? true : false;
  }

  /**
   * Has One
   * 
   * Define one-to-one entity relationship (1-1)
   * 
   * @param array $friend  Associative array of attributes
   *  $child has the form --> ['table'=> string of child table name,
  *                            'foreign_key' => string of foreign key
  *                                                      column name]
   * 
   * @return object   Object of the class
   */
  public function hasOne($friend) {
    $table = $friend['friend'];
    $foreign_key = $friend['foreign_key'];

    $this->foreign = [
      'table' => $table,
      'data' => [$foreign_key => $this->preserved]
    ];
    $this->rtype = '1-1';
    
    return $this;
  }

  /**
   * Has Many
   * 
   * Define child entity relationship (1-*)
   * 
   * @param array $child  Associative array of attributes
   *  $child has the form --> ['table'=> string of child table name,
  *                            'foreign_key' => string of foreign key
  *                                                      column name]
   * 
   * @return object   Object of the class
   */
  public function hasMany($child) {
    $table = $child['child'];
    $foreign_key = $child['foreign_key'];

    $this->foreign = [
      'table' => $table,
      'data' => [$foreign_key => $this->preserved]
    ];
    $this->rtype = '1-m';
    
    return $this;
  }

  /**
   * Belongs To
   * 
   * Define parent entity relationship (*-1)
   * 
   * @param array $parent  Associative array of attributes
   *  $parent has the form --> ['table'=> string of child table name,
   *                            'ref_key' => string of referenced key
   *                                                      column name]
   * 
   * @return object   Object of the class
   */
  public function belongsTo($parent) {
    $table = $parent['parent'];
    $ref_key = $parent['foreign_key'];

    $this->foreign = [
      'table' => $table,
      'data' => [$ref_key => $this->preserved]
    ];
    $this->rtype = 'm-1';

    return $this;
  }

  /**
   * Has Pivot With
   * 
   * Define many to many entity relationship (*-*)
   * 
   * @param array $relation  Associative array of attributes
   *  $relation has the form --> ['relative'=> string of other table name,
   *                              'pivot' => string of pivot table name,
   *                            'relative_key' => string of other table 
   *                                              referenced key column name
   *                            'ref_key' => string of referenced key
   *                                                      column name]
   * 
   * @return object   Object of the class
   */
  public function hasPivotWith($relation) {
    $relative = $relation['relative'];
    $pivot = $relation['pivot'];
    $relative_key = $relation['relative_key'];
    $ref_key = $relation['ref_key'];

    $this->relation = [
      'relative_table' => $relative,
      'pivot_table' => $pivot,
      'relative_key' => $relative_key,
      'data' => [$ref_key => $this->preserved]
    ];
    $this->rtype = 'm-m';

    return $this;
  }

  /**
   * Get Related Records
   * 
   * Retrieve records that are related to
   * the entity from the database
   * 
   * @param array|string (oprional) $filters   Associative array or 
   *                                           string of filters
   *  $filters has the form --> ['name_of_filter' => array_of_values]
   * 
   * @return array Array of database record objects
   */
  public function getR($filters = []) {
    switch ($this->rtype) {
      case 'm-1':
        $this->db->where($this->foreign['data']);
        $result = $this->db->get($this->foreign['table'])->row();

        return $result;
        break;

      case '1-1':
        $this->db->where($this->foreign['data']);
        $result = $this->db->get($this->foreign['table'])->row();

        return $result;
        break;
      
      default:
        $this->db->where($this->foreign['data']);
        $result = $this->db->get($this->foreign['table'])->result();

        return $result;
        break;
    }
  }

  /**
   * Get Related Records count
   * 
   * Retrieve records that are related to
   * the entity from the database
   * 
   * @param array|string (oprional) $filters   Associative array or 
   *                                           string of filters
   *  $filters has the form --> ['name_of_filter' => array_of_values]
   * 
   * @return int  Number of records retreived
   */
  public function getRCount($filters = []) {
    switch ($this->rtype) {
      default:
        $this->db->where($this->foreign['data']);
        $result = $this->db->count_all_results($this->foreign['table']);

        return $result;
        break;
    }
  }

    // if($this->foreign) {
    //   if(!empty($filters)) {
    //     $this->applyFilters($filters);
    //   }
    //   $this->db->where($this->foreign['data']);
    //   $results = $this->db->get($this->foreign['table'])->result();

    //   return $results;
    // }
    
    // if($this->relation) {
      
    //   $this->db->where($this->relation['data']);
    //   $pivots = $this->db->get($this->relation['pivot_table'])->result();
      
    //   foreach($pivots as $pivot) {
    //     $id = $pivot->{$this->relation['relative_key']};
    //     $this->db->where('id', $id);

    //     $result = $this->db->get($this->relation['relative_table'])->row();

    //     array_push($results, $result);
    //   }

    //  return $results;
    //}

  /**
   * Create Related Records
   * 
   * Create new database record and associated
   * related records
   * 
   * @param array $data   Associative array of data
   * 
   * @return bool   True if successful and False if otherwise
   */
  public function createR($data) {
    switch ($this->rtype) {
      case 'm-m':
        $base_table = $this->table;
        $base_data = $data['base_data'];

        $res = $this->db->insert($this->table, $base_data);
        if($res) {
          $pivot_table = $this->relation['pivot_table'];
          $pivot_data = $data['pivot_data'];

          return $this->db->insert_batch($pivot_table, $pivot_data) ? true : false;
        }
        else {
          return false;
        }
        break;
      
      case '1-m':
        $base_table = $this->table;
        $base_data = $data['base_data'];
        
        $res = $this->db->insert($this->table, $base_data);
        if($res) {
          $child_table = $this->foreign['table'];
          $child_data = $data['child_data'];

          return $this->db->insert_batch($child_data, $child_data) ? true : false;
        }
        break;

      default:
        
        break;
    }
  }

  /**
   * Delete Related Record(s)
   * 
   * Delete related database record(s)
   * 
   * @return bool True if successful and False if otherwise
   */
  public function deleteR() {
    switch ($this->rtype) {
      default:
        $this->db->where($this->foreign['data']);
        $delete = $this->db->delete($this->foreign['table']) ? true : false;

        return $delete;
        break;
    }
  }

  /**
   * Apply Filters
   * 
   * Apply a query filter to the database
   * object
   * 
   * @param array|string $filters   Associative array or 
   *                                string of filters
   *  $filters has the form --> ['name_of_filter' => array_of_values]
   * 
   * @return void
   */
  public function applyFilters($filters) {
    foreach($filters as $filter) {
      $key = key($filters);
      
      switch ($key) {
        case 'order_by':
          $filter_key = key($filter);
          $this->db->order_by($filter_key, $filter[$filter_key]);
          break;
        
        default:
          $this->db->{$key}($filter);
          break;
      }

      next($filters);
    }
  }

  /**
   * Clear Variable
   * 
   * Clear the value of a specified variable.
   * Make it an empty string
   * 
   * @param mixed $var   Variable to clear
   * 
   * @return void
   */
  public function clr($var) {
    $this->{$var} = '';
  }
}

/* End of file Ace_Model.php */