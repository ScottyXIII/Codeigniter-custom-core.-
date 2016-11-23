<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model 
{

	// ADD CACHING OF QUERIES.
	// Return object or array.

	protected $table = ""; 

	public function __construct()
	{
		parent::__construct(); 

		$this->load->database();
		$this->set_table();
	}

	/**
	*	Try any undefinded calls to the CI db class.
	*
	*/
	public function __call($method, $args = null)
	{	
		if (method_exists($this->db, $method)) {
			if ($args == null) {
				$this->db->{$method};
			} else {
				call_user_func_array(array($this->db, $method), $args);
			}
		}

		return $this;
	}

	/**
	* Sets the table this model represents in the database.
	*
	* @param string $table_name
	* @return void  
	*/
	public function set_table($table_name = null) 
	{
		if ($table_name == null) {
			$replace_vales = array("m_", "model_", "_model", "_m");
			$table = str_replace ($replace_vales, "", strtolower (get_called_class()));
			$this->table = $table; 
		} else {
			$this->table = $table_name; 
		}
	}


	/**
	* 	Returns all records from this table
	*
	*	@param int $limit 
	*	@return CI_ActiveRecord
	*/
	public function all($limit = 0) 
	{
		$query = $this->db->get($this->table, null, $limit);

		return $query->result();
	}

	/**
	* 	Find a singular record.
	*
	*	@param int $id 
	*	@return bool
	*/
	public function find($value, $column = "id") 
	{
		$this->db->get($this->table);  

		$query = $this->db->get_where($this->table, array($column => $value));
		
		return $query->row(0, get_called_class());
	}

	/**
	*	Finds multiple records.
	*
	*	@param mixed $value
	* 	@param string $column
	*/
	public function finds($value, $column) 
	{
		$query = $this->db->get_where($this->table, array($column => $value));

		return $query->result(get_called_class());
	}

	/**
	* 	Adds a where statment to our query. 
	*
	*	@param array $values 
	*	@return bool
	*/
	public function where($column, $operator, $value) 
	{
		if  (is_array($value)) {

			foreach($value as $val) {
				$this->db->where($column . ' ' . $operator, $val);
			}

		} else {
			$this->db->where($column . ' ' . $operator, $value);
		}

		return $this;
	}

	/**
	* 	Returns results of current query.
	*
	*	@return object | bool
	*/
	public function results() 
	{
		
		$query = $this->db->get($this->table);

		if (count($query->result()) <= 0) {
			return false; 
		} 

		return $query->result(get_called_class());
	}

	/**
	* 	Inserts into the database
	*
	*	@param array $values 
	*	@return int $id
	*/
	public function insert($values) 
	{
		$this->db->insert($this->table, $values);	

		return $this->db->insert_id();
	}

	public function delete($id)
	{
		
	}

	/**
	* 	Updates a value in the database
	*
	*	@param array $values 
	*	@return bool
	*/
	public function update($values, $where_column = null, $where_value = null) 
	{
		if ($where_column != null) {
			$this->db->where($where_column, $where_value);
		}

		$this->db->update($this->table, $values);

		return $this->db->affected_rows();
	}

	/**
	 * Builds a foreign key for this table
	 * 
	 *  @param string $key 
	 *  @return string
	 */
	private function get_foreign_key($key = 'id') 
	{
		$return = $key;
		if ($key == 'id') {
			$return = $this->table . '_id';
		} 
		return $return;
	}

	/**
	* 	HasOne relationship. 
	*
	*	Looks for all results in $table where this model's id == foreign_ket
	* 	Assume forgien key to be $table . '_id' returns object model.
	*	
	*  @param string $table
	*  @param string $foregin_key
	*  @param string $primarykey 
	*  @return object
	*/
	public function has_one($table, $foreign_key = 'id', $primary_key = 'id') 
	{
		$foreign_key = $this->get_foreign_key($foreign_key);

		$query = $this->db->get_where($table, array($foreign_key => $this->{$primary_key}));

		return $query->row(0, 'M_'.$table);
	}

	/**
	 * 	HasMany relationship. Looks 
	 * 
	 *  @param string $table
	 *  @param string $foregin_key
	 *  @param string $primarykey 
	 *  @return object
	 */
	public function has_many($table, $foreign_key = 'id', $primary_key = 'id') 
	{
		$foreign_key = $this->get_foreign_key($foreign_key);

		$this->load->model("M_".$table, 'model');
		$this->model->where($foreign_key, '=', $this->{$primary_key});
		
		return $this->model;// $this->db->where($foreign_key, $this->{$primary_key})->order_by('id', 'desc')->where('id >', 0)->get($table)->result();
	}

	/**
	 * 	Many To Many relationship. Looks for at connected table for all $forgien_keys == thisModel's id.
	 * 
	 *  @param string $table
	 *  @param string $connect_table
	 *  @param string $foregin_key
	 *  @param string $primarykey 
	 *  @return object
	 */
	public function belongs_to_many($table, $connect_table = '', $primary_key = 'id', $foreign_key = 'id') 
	{
		$foreign_key = $this->get_foreign_key($foreign_key);

		if ($connect_table == '') {
			$connect_table = $this->table . '_' . $table;
		}
		if ($primary_key == '') {
			$primary_key = $table . '_id';
		}
		if ($foreign_key == '') {
			$foreign_key = $this->table . '_id';
		}
		
		$this->db->select('id');
		$this->db->where($foreign_key, $this->{'id'});
		$many = $this->db->get($connect_table)->result();

		$ids = array();
		foreach ($many as $one) {
			$ids[] = $one->id; 
		}

		
		if (class_exists("M_".$table)) {
			$this->load->model("M_".$table, 'model');
			$this->model->where_in('id', $ids);
			
			$this->model->get($table);

			return $this->model;
		} else {
			$this->db->where_in('id', $ids);
			$query = $this->db->get($table);
			
			return $query->result();
		} 

		
	}

	 /**
	 *  Tries to load the given model else just return CI db object.  
	 *
	 **/
	private function load_model($model, $new_name) 
	{
		$model = $this->db;
		if (class_exists($model)) {
			$model = $this->load->model($model, $new_name);
		}

		return $model;
	}

}