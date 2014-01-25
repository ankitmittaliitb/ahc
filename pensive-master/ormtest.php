<?php
include 'lib/orm.php' ;
include_once('config.php');

class OrmTest extends PHPUnit_Framework_TestCase {
	//database connection checking 
	public function test_connect_db(){
		try{
			$db = connect_db();
			$this->assertTrue((bool) $db);
		}
		catch (Exception $e){
			$this->fail('Unable to connect');
		}
	}
	// get_columns from a table 
	public function test_get_columns() {
		$this->assertEquals(array("0"=>"id","1"=>"user","2"=>"pass","n"=>3),get_columns($table='login'));
	}
	// normalise parameter with values replaceing ?
	public function test_normalise_params() {
		$db = connect_db() ;
		$sql = "INSERT INTO login(user,pass) VALUES (?, ?)";
		$params = array('Amit', 'password');
		$this->assertEquals("INSERT INTO login(user,pass) VALUES ('Amit', 'password')",(string)normalise_params($sql,$params)) ;
	}
	// deleting records from table where condition matches 
	public function test_delete_records() {
		$table = 'login' ;
		$conditions = array('pass'=>'LBL');		
		//$this->assertTrue(delete_records($table,$conditions)) ;
	}
	// deleting records where selection true
	public function test_delete_records_select() {
		$table ='login' ;
		$select = 'id>? AND user=? AND pass=?' ;
		$params = array(26, 'Amit Patel','n0wL!nux') ;
		//$this->assertTrue(delete_records_select($table, $select, $params)) ;
	}
	// deleting records getting inpur from list
	public function test_delete_records_list() {
		$table ='login' ;
		$field = 'user' ;
		$values = array('Sunil Patel','Vikas Patel');
		//$this->assertTrue(delete_records_list($table,$field,$values)) ;
	}
	// getting the number of rows once upto 10 
	public function test_get_records_sql() {
		$sql = "SELECT * FROM login where user =?" ;
		$params = array('Amit Patel') ;
		$limitfrom = 0 ;
		$limitnum = 10 ;
		$this->assertEquals(4,count((get_records_sql($sql,$params,$limitfrom,$limitnum))));
	}
	// excecute the query 
	public function test_execute() {
		$sql = "INSERT INTO login(user,pass) values(?,?);"; // for bad sql 
		$sql = "INSERT INTO login(user,pass) values(?,?)";		
		$params = array('Vikas Patel','EnterPass') ;
		//$this->assertTrue(execute($sql,$params)) ;
	}
	// counts the existing records of the match
	public function test_count_records_sql(){
		$sql = "SELECT count(*) FROM login WHERE user=?" ;
		$params = array('Amit Patel') ;
		$this->assertEquals(4,count_records_sql($sql,$params)) ;
	}
	// getting the rows of field
	public function test_get_field_sql(){
		$sql = "SELECT count(*) FROM login WHERE user=?" ;
		$params = array('Amit Patel') ;
		$this->assertEquals(4,get_field_sql($sql,$params)) ;
	}
	// counting the records where select meets
	public function test_count_records_select(){
		$table ='login' ;		
		$select = 'user=?';	
		$params = array('Amit Patel') ;
		$countitem = "count(*)" ;
		$this->assertEquals(4,count_records_select($table,$select,$params,$countitem)) ;
	}
	// updating the record by getting id as the primary field
	public function test_update_record_raw(){
		$table = 'login' ;
		$params = array('id'=>'41','user'=>'Vikas Patel','pass'=>'VikasPass') ;
		//$this->assertTrue(update_record_raw($table,$params)) ;
	}
	// counts the records where condition matches 
	public function test_count_records(){
		$table = 'login' ;
		$conditions = array('user'=>'Amit Patel','pass'=>'n0wL!nux') ;	
		$this->assertEquals(1,count_records($table,$conditions)) ;
	}
	// inserting record into table and returning id value
	public function test_insert_record_raw(){
		$table = 'login' ;
		$params = array('user'=>'Sunil Patel','pass'=>'sunilpass') ;
		//$this->assertEquals(64,insert_record_raw($table,$params,$returnid=true,$bulk=false));
	}
	// testing on where clause 
	public function test_where_clause() {
		$table = 'login' ;
		$conditions = array('id'=>'53','user'=>'Amit Patel','pass'=>'n0wL') ;
		$this->assertEquals(2,count(where_clause($table,$conditions)));
	}
	// test case for the where_clause_list
	public function test_where_clause_list() {
		$field = 'user' ;
		$values = array('Amit','Sachin','Sunil') ;
		$this->assertEquals(2,count(where_clause_list($field,$values))) ;
	}
	// test case for disconnecting the database
	public function test_disconnect_db() {
		$db = connect_db() ;
		$this->assertTrue(disconnect_db($db)) ;
	}




}

?>

