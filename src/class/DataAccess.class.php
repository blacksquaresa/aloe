<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * The DataAccess class provides methods to perform database actions on a particular table within a database. 
 * 
 * @package Classes
 * @subpackage Data Access
 * @since 2.0
 */
class DataAccess{
	
	// {{{ Declarations
	/**
	 * The ID of the internal PHP data connection, used by all PHP mysql commands
	 *
	 * @var int 
	 *
	 */
	private $connection;
	
	/**
	 * The server name (either domain or IP address)
	 *
	 * @var string 
	 *
	 */
	private $dbserver;
	
	/**
	 * The name of the database schema
	 *
	 * @var string 
	 *
	 */
	private $dbdatabase;
	
	/**
	 * The username to use to connect to the database
	 *
	 * @var string 
	 *
	 */
	private $dbusername;
	
	/**
	 * The password to use to connect to the database
	 *
	 * @var string 
	 *
	 */
	private $dbpassword;
	#endregion	
	
	// {{{ Constructor	
	/**
	 * Create a new instance of this class, whcih represents a connection to a single schema in a database
	 *
	 * @param string $dbserver The host name of the server (either domain or ip address)
	 * @param string $dbdatabase The name of the database schema
	 * @param string $dbusername The username to use for access
	 * @param string $dbpassword The password to use for access
	 */
	function __construct($dbserver,$dbdatabase,$dbusername,$dbpassword){		
		$this->dbserver = $dbserver;
		$this->dbdatabase = $dbdatabase;
		$this->dbusername = $dbusername;
		$this->dbpassword = $dbpassword;		
	}
	#endregion	
	
	// {{{ Connect
	/**
	 * Create a connection to the database, and assign it to the connection property
	 *
	 * @param string $dbserver The host name of the server (either domain or ip address)
	 * @param string $dbdatabase The name of the database schema
	 * @param string $dbusername The username to use for access
	 * @param string $dbpassword The password to use for access
	 * @return mixed The ID of PHP connection object, or false on failure.
	 */
	public function dbConnect($dbserver,$dbdatabase,$dbusername,$dbpassword){
		
		if(!($this->connection = mysql_connect($dbserver, $dbusername, $dbpassword, true))){
			trigger_error('Failed to connect to server ' . $dbserver . ' : ' . mysql_error(),E_USER_WARNING);
			return false;
		}	
		if (!mysql_select_db($dbdatabase, $this->connection)) {
			trigger_error('Failed to connect to database ' . $dbdatabase . ' : ' . mysql_error(),E_USER_WARNING);
			return false;
		}		
		return $this->connection;
	}	
	#endregion	
	
	// {{{ Insert or Update	
	/**
	 * Use this method to insert the record if it does not already exist, and update it if it does.
	 *
	 * @param string $tablename The name of the table
	 * @param array $values An associative array of values. The key should be the field name
	 * @param array $pks An associative array of primary keys. The key should be the field name, and the value the required value of the PK
	 * @param string $retcol The method will return the value of this field. Usually the ID of the row, for inserts.
	 * @param boolean $handlenull If true, null values will be inserted as "null", not as empty values.
	 * @return string The value of the return column, or false on failure
	 */
	public	function insertupdate($tablename,$values,$pks,$retcol='id',$handlenull=false){
		if(empty($retcol)) $retcol = 'id';
		$selsql = "select $retcol from $tablename where ";
		foreach($pks as $col => $val){
			$val = mysql_real_escape_string(stripslashes($val));
			$selsql .= "`$col` = '$val' and ";
		}
		$selsql = substr($selsql,0,strlen($selsql)-5);
		$res = $this->performquery($selsql,'Failed to check for existing rows in table: ','res');
		if($res === false) return false;
		if(mysql_num_rows($res) >= 1){
			if($this->update($tablename,$values,$pks,$handlenull)){
				$id = mysql_result($res,0,$retcol);
			}else{
				$id = false;
			}
		}else{
			$id = $this->insert($tablename,$values,$handlenull);
		}
		mysql_free_result($res);
		return $id;
	}
	#endregion	
	
	// {{{ Insert
	/**
	 * Inserts a row of values into a table
	 *
	 * @param string $tablename The name of the table
	 * @param array $values An associative array of values to be inserted. The key should be the name of the field
	 * @param bool $handlenull If true, null values will be inserted as null, and not as an empty string
	 * @return mixed The ID of the inserted row, or false on failure
	 */
	public function insert($tablename,$values,$handlenull=false){
		$inssql = "insert into $tablename (";
		$valsql = '';
		foreach($values as $col => $val){
			if($handlenull && $val === null){
				$inssql .= "`$col`,";
				$valsql .= "null,";
			}else{
				$val = mysql_real_escape_string(stripslashes($val));
				$inssql .= "`$col`,";
				$valsql .= "'$val',";
			}
		}
		$inssql = rtrim($inssql,',') . ") values(" . rtrim($valsql,',') . ")";
		$res = $this->performquery($inssql,'Failed to insert values into table: ','id');
		return $res;
	}
	
	/**
	 * Use this method to insert the record if it does not already exist.
	 *
	 * @param string $tablename The name of the table
	 * @param array $values An associative array of values. The key should be the field name
	 * @param array $pks An associative array of primary keys. The key should be the field name, and the value the required value of the PK
	 * @param boolean $handlenull If true, null values will be inserted as "null", not as empty values.
	 * @return string The id of the record in the database, whether inserted or not, or false on failure
	 */
	public	function insertifnotexists($tablename,$values,$pks,$handlenull=false){
		$selsql = "select id from $tablename where ";
		foreach($pks as $col => $val){
			$val = mysql_real_escape_string(stripslashes($val));
			$selsql .= "`$col` = '$val' and ";
		}
		$selsql = substr($selsql,0,strlen($selsql)-5);
		$res = $this->performquery($selsql,'Failed to check for existing rows in table: ','res');
		if($res === false) return false;
		if(mysql_num_rows($res) >= 1){
			$id = mysql_result($res,0,'id');
		}else{
			$id = $this->insert($tablename,$values,$handlenull);
		}
		mysql_free_result($res);
		return $id;
	}
	#endregion	
	
	// {{{ Update
	/**
	 * Update an existing row in the database
	 *
	 * @param string $tablename The name of the table containing the row
	 * @param array $values An associative array of values for the fields in the row. Only these fields will be updated - all others will be left as is.
	 * @param array $pks An associative array of primary keys used to identify the row. 
	 * @param bool $handlenull If true, null values will be set to null, rather than an empty string
	 * @return bool True for success, false otherwise
	 */
	public function update($tablename,$values,$pks,$handlenull=false){	
		$updsql = "update $tablename set ";
		foreach($values as $col => $val){
			if($handlenull && $val === null){
				$updsql .= "`$col` = null,";
			}else{
				$val = mysql_real_escape_string(stripslashes($val));
				$updsql .= "`$col` = '$val',";
			}
		}
		if(count($pks) > 0){
			$updsql = rtrim($updsql,',') . " where ";
			foreach($pks as $col => $val){
				if($handlenull && $val === null) $updsql .= "`$col` is null and ";
				else{
					$val = mysql_real_escape_string(stripslashes($val));
					$updsql .= "`$col` = '$val' and ";
				}
			}
		}
		$updsql = substr($updsql,0,strlen($updsql)-5);
		$res = $this->performquery($updsql,'Failed to update table: ','res');
		if($res === false) return false;
		return true;
	}
	#endregion	
	
	// {{{ Select Methods
	/**
	 * Perform a simple select query
	 *
	 * @param string $sqlquery the complete SQL query
	 * @return array a two-dimensional array representing all the rows of the result, each of which is an array of values.
	 */
	public function select($sqlquery){	
		$res = $this->performquery($sqlquery,'Failed to perform select query: ','res');
		if($res === false) return false;
		$id = array();
		while($row = mysql_fetch_assoc($res)){
			$id[] = $row;
		}
		mysql_free_result($res);
		return $id;
	}
	
	/**
	 * Select a single value from the database. 
	 *
	 * @param string $sqlquery The complete SQL for the select query. If it does not include a limit, a limit of 1 row will automatically be added
	 * @return mixed The single result. If the query returns more than 1 row, the first row is taken. If the query returns more then one field per row, the first value will be returned
	 */
	public function selectsingle($sqlquery){
		if(strtolower(substr($sqlquery,-7,7)) != "limit 1") $sqlquery .= " Limit 1";
		$res = $this->performquery($sqlquery,'Failed to perform select single query: ','res');
		if($res === false) return false;
		if(mysql_num_rows($res) >= 1){
			$id = mysql_result($res,0);
		}else{
			$id = false;
		}
		mysql_free_result($res);
		return $id;
	}
	
	/**
	 * Perform a select query, and return an associative array where the keys are the value of a selected field
	 *
	 * @param string $sqlquery The full SQL query to be performed
	 * @param string $colid The name of the column to use for the index of the return array
	 * @return mixed An associative array containing the retuened rows of the query, with the keys set to the value of the supplied field.
	 */
	public function selectindex($sqlquery,$colid='id'){
		$res = $this->select($sqlquery);
		if(is_array($res)){
			$arr = array();
			foreach($res as $row){
				$arr[$row[$colid]] = $row;
			}
			return $arr;
		}
		return false;
	}
	
	/**
	 * Select a single row from a database. Returns an array of values, rather than a two-dimensional array of rows. 
	 *
	 * @param string $sqlquery The SQL query to be performed
	 * @return array An associative array of fields=>values. If the query returns more than one row, the first will be returned.
	 */
	public function selectrow($sqlquery){
		$res = $this->select($sqlquery);
		if(is_array($res) && count($res)) return $res[0];
		return false;
	}
	
	/**
	 * Select the values of a specific field return by this query
	 *
	 * @param string $sqlquery The full SQL query to perform
	 * @param string $colid The name of the field whose values should be returned
	 * @return array An array containing the values of the field returned by the query
	 *
	 */
	public function selectcolumn($sqlquery,$colid='id'){
		$res = $this->select($sqlquery);
		if(is_array($res)){
			$arr = array();
			foreach($res as $row){
				$arr[] = $row[$colid];
			}
			return $arr;
		}
		return false;
	}
	#endregion	
	
	// {{{ Delete
	/**
	 * Delete one or more rows from a table
	 *
	 * @param string $tablename The name of the table
	 * @param mixed $id the value of the Primary Key field of the row or rows to be deleted
	 * @param string $pk The name of the primary key field to check
	 * @return mixed the number of rows affected, or false on error
	 */
	public function delete($tablename,$id,$pk='id'){
		$res = $this->performquery("delete from $tablename where `$pk` = '$id'",'Failed to perform delete query: ','aff');	
		return $res;
	}
	#endregion	
	
	// {{{ Execute
	/**
	 * Execute a SQL command that isn't covered by any of the built-in methods
	 *
	 * @param string $sqlquery The SQL command to execute
	 * @return mixed The number of rows affected, or false on failure
	 */
	public function execute($sqlquery){
		$res = $this->performquery($sqlquery,'Failed to execute query: ','aff');
		if($res === false) return false;
		return $res;
	}
	#endregion	
	
	// {{{ Perform Query
	/**
	 * Internal method to actually perform the queries built or supplied by public methods
	 *
	 * @param string $sqlquery The SQL query to perform
	 * @param string $errorstring The string to use in the error message should the query fail
	 * @param string $returntype The type of return data. This could be: 
	 *		"res" - the result, usually a two dimensional array of rows (default)
	 *		"id" - the id of the last inserted row
	 *		"aff" - the number of rows affected by the query
	 * @return mixed The value requested by the return type
	 */
	private function performquery($sqlquery,$errorstring,$returntype='res'){

		if(!$this->connection ||(empty($this->connection))){
			$this->dbConnect($this->dbserver,$this->dbdatabase,$this->dbusername,$this->dbpassword);
		}
		
		$res = mysql_query($sqlquery,$this->connection);
		if($res === false) {
		    trigger_error($errorstring . mysql_error(),E_USER_WARNING);
			return false;
		}
		$ret = $res;
		if($returntype == 'id') $ret = mysql_insert_id($this->connection);
		if($returntype == 'aff') $ret = mysql_affected_rows();
		return $ret;	
	}
	#endregion	
	
	// {{{ Transactions
	/**
	 * Begin a transaction
	 * 
	 * Once this method is called, all subsequent database calls will be run on this transaction, and all will be rolled back unless an explicit commit call is made.
	 *
	 * @return bool True on success, false otherwise
	 */
	public function begintransaction(){
		return $this->performquery("start transaction;","Failed to begin the transaction",'res');
	}
	
	/**
	 * Commit a transaction previously begun
	 *
	 * @return bool True on success, false otherwise
	 */
	public function committransaction(){
		return $this->performquery("commit;","Failed to commit the transaction",'res');	
	}
	
	/**
	 * Roll back a transaction previously begun
	 *
	 * @return bool True on success, false otherwise
	 */
	public function rollbacktransaction(){
		return $this->performquery("rollback;","Failed to rollback the transaction",'res');
	}
	#endregion	
	
	// {{{ Where strings for searches
	/**
	 * Create a "where" string to include with a SQL query based on a string search
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for each word in the supplied string against the selected field using a LIKE comparison, and if any one passes, the row is returned
	 *
	 * @param string $string The string to use for comparison
	 * @param string $column The name of the field to check against the supplied string
	 * @param string $table1 The name of the table containing the field. This might be the full name, or an alias.
	 * @param string $table2 The name of an optional second table containing a field of the same name, or an alias
	 * @param string $where The current where string. The result will include and expand this.
	 * @param string $connector How this search should relate to the existing where statement. Default "and", but might also be "or"
	 * @return string The modified "where" statement
	 */
	public function getSearchStringWhere($string,$column,$table1='',$table2=null,$where,$connector='and'){
		if(!empty($string)){
			if(!empty($table1)) $table1 .= '.';
			$where .= ($where==''?' where':' '.$connector);
			$parts = explode(' ',strtolower($string));
			$plist = '';
			foreach($parts as $p){
				$p = mysql_real_escape_string($p);
				if(!empty($plist)) $plist .= ' OR';
				$plist .= " lcase($table1$column) like '%$p%'";
				if(!empty($table2)) $plist .= " OR lcase($table2.$column) like '%$p%'";
			}
			$where .= " ($plist)";
		}
		return $where;
	}
	
	/**
	 * Create a where statement for a SQL query, checking that a field equals the supplied value exactly
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for that the entire string supplied is exactly the value of the selected field
	 *
	 * @param string $string The exact string to check our field against
	 * @param string $column the name of the field to check
	 * @param string $table1 the name of the table containing the field (full name or alias)
	 * @param string $table2 Optional second table name (full name or alias)
	 * @param string $where The orignal where statement, to which this new statement should be appended
	 * @param string $connector The connector to use when adding this statement to previous statements
	 * @param bool $casesensitive Whether or not the check should be case sensitive
	 * @return string The ammended where statement
	 */
	public function getSearchStringExactWhere($string,$column,$table1='',$table2=null,$where,$connector='and',$casesensitive=false){
		if(!empty($string)){
			if(!empty($table1)) $table1 .= '.';
			$where .= ($where==''?' where':' '.$connector);
			if($casesensitive){
				$string = mysql_real_escape_string($string);
				$where .= " $table1$column = '$string'";
				if(!empty($table2)) $where .= " OR $table2.$column = '$string'";
			}else{
				$p = strtolower($string);
				$p = mysql_real_escape_string($p);
				$where .= " lcase($table1$column) = '$p'";
				if(!empty($table2)) $where .= " OR lcase($table2.$column) = '$p'";
			}
		}
		return $where;
	}
	
	/**
	 * Create a where statement for a SQL query, checking that a field equals the supplied integer exactly
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for that the integer supplied is compares to the field value correctly
	 *
	 * @param int $int The integer to use for comparison
	 * @param string $column The field to compare to the supplied integer
	 * @param string $table The name of the table containing the field
	 * @param string $where The original where statement, to which the  new statement should be appended
	 * @param string $connector The connector to use when appending the new statement to the original
	 * @param string $comparer the operation to use when comparing the integer to the field. Default is "="
	 * @return string The modified where statement
	 */
	public function getSearchIntWhere($int,$column,$table,$where,$connector='and',$comparer='='){
		if(is_numeric($int)){
			if(!empty($table)) $table .= '.';
			$where .= ($where==''?' where':' '.$connector);
			$where .= " $table$column $comparer $int";
		}
		return $where;
	}
	
	/**
	 * Create a where statement for a SQL query, checking that a field equals the supplied date exactly
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for that the date supplied is compares to the field value correctly. 
	 * If the supplied date is not a unix timestamp, the method will attempt to convert it into one
	 * For start checks, the supplied date must be greater or equal to the field value.
	 * For end checks, the supplied date must be less than the field vale.
	 *
	 * @param mixed $date a string date, or a unix timestamp
	 * @param string $type Whether this is a starting or ending date ("start" or "end")
	 * @param string $column The field to check the date against
	 * @param string $table The name of the table
	 * @param string $where The original where statement
	 * @return string The modified where statement
	 */
	public function getSearchDateWhere($date,$type,$column='date',$table,$where){
		if(!empty($date)){
			$ed = strtodate($date);
			$join = "=";
			if(!empty($ed) && $ed > 0){
				switch($type){
					case 'start':	
						$ed = mktime(0,0,0,date('m',$ed),date('d',$ed),date('Y',$ed));
						$join = ">=";
						break;
					case 'end':	
						$ed = mktime(23,59,59,date('m',$ed),date('d',$ed),date('Y',$ed));
						$join = "<=";
						break;
				}	
				if(!empty($table)) $table .= '.';
				$where .= (empty($where)?' where':' and');
				$where .= " $table$column $join $ed";
			}
		}
		return $where;
	}
	
	/**
	 * Create a where statement for a SQL query, checking that a field equals one of the supplied strings exactly
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for that the field value equals any one of the string values in the array. 
	 *
	 * @param string[] $array An array containing a set of strings to use for comparison
	 * @param string $column The name of the field
	 * @param string $table The name of the table
	 * @param string $where The original where statement
	 * @return string The modified where statement
	 */
	public function getSearchStringArrayWhere($array,$column,$table,$where){
		if(!empty($array)){
			if(!is_array($array)){
				$array = explode(',',$array);	
			}
			$list = '';
			foreach($array as $item){
				if(!empty($list)) $list .= ',';
				$list .= "'" . mysql_real_escape_string(trim($item,"'")) . "'";
			}
			if(!empty($list)){	
				if(!empty($table)) $table .= '.';
				$where .= ($where==''?' where':' and');
				$where .= " $table$column in ($list)";
			}
		}	
		return $where;
	}
	
	/**
	 * Create a where statement for a SQL query, checking that a field equals one of the supplied integers exactly
	 * 
	 * Use the Where Search methods to build a where statement for a SQL query. 
	 * Each method will add one comparison to the where statement, so build up a complex where statement by making multiple calls to these methods
	 * Each method will check the comparison value first, and return the where statement unchanged if that value is not appropriate (eg: empty)
	 * 
	 * This method will check for that the field value equals any one of the integer values in the array. 
	 *
	 * @param int[] $array An array containing the set of numbers to compare against
	 * @param string $column The field to compare
	 * @param string $table The name of the table
	 * @param string $where The original where statement
	 * @return string The modified where statement
	 */
	public function getSearchIntArrayWhere($array,$column,$table,$where){
		if(!empty($array)){
			if(!is_array($array)){
				$array = explode(",",$array);	
			}	
			$list = '';
			foreach($array as $item){
				if(is_numeric($item)){
					if(!empty($list)) $list .= ',';
					$list .= $item;
				}
			}
			if($list!=''){	
				if(!empty($table)) $table .= '.';
				$where .= ($where==''?' where':' and');
				$where .= " $table$column in ($list)";
			}
		}	
		return $where;
	}
	#endregion	
}

?>