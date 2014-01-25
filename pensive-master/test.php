<?
//file for testing functions
include_once('config.php');
include_once('lib/orm.php');
global $db ;
connect_db();

function test_connect_db() {
	return test_function('connect_db');
}

//print_r(test_connect_db()) ."<br>";

function test_disconnect_db($db=null) {
	if ($db) {
		return test_function('disconnect_db', array($db));
	} else {
		return test_function('disconnect_db', array(connect_db()), FALSE);
	}
}

//print_r(test_disconnect_db($db=null)) ."<br>"; 

function test_normalise_params($sql=null, $params=null) {
	if(!$sql) {
		$sql = "INSERT INTO login(user,pass) VALUES (?, ?)";
	}

	if(!$params) {
		$params = array('Amit', 'password');
	}

	$result = test_function('normalise_params', array($sql, $params));
	return $result;
}

//print_r(test_normalise_params($sql=null, $params=null)) ."<br>"; 

function test_delete_records($table=null,array $conditions=null){
	if(!$table){
		$table = 'login' ;
	}
	if(!$conditions){
		$conditions = array('user'=>'Vikas Patel','pass'=>'');		
	}
	$result = test_function('delete_records',array($table,$conditions)) ;
	return $result ;
}

//print_r(test_delete_records($table=null,$conditions=null)) ."<br>"; 

function test_delete_records_select($table=null,$select,array $params=null){
	if(!$table){
		$table ='login' ;
	}
	if(empty($select)){
		$select = 'id>? AND user=? AND pass=?' ;
	}
	if(!$params){
		$params = array(5, 'Ankit','sir');
	}
	$result = test_function('delete_records_select',array($table,$select,$params)) ;
	return $result ;
}

//print_r(test_delete_records_select($table=null,$select,$params=null)) ."<br>"; 

function test_delete_records_list($table=null,$field=null,array $values=null){
	if(!$table){
		$table ='login' ;
	}
	if(!$field){
		$field = 'user' ;
	}
	if(!$values){
		$values = array('','Amit Patel','Sunil Patel');
	}
	$result = test_function('delete_records_list',array($table,$field,$values)) ;
	return $result ;
}

//print_r(test_delete_records_list($table=null,$field=null,$values=null)) ."<br>"; 

function test_get_records_sql($sql=null,array $params=null, $limitfrom=0,$limitnum=null){
	if(!$sql){
		$sql = "SELECT * FROM login where user =?" ;		
	}
	if(!$params){
		$params = array('Amit Patel') ;
	}
	if(!$limitfrom){
		$limitfrom = 0 ;
	}
	if(!$limitnum){
		$limitnum =10;
	}
	$result = test_function('get_records_sql',array($sql,$params,$limitfrom,$limitnum)) ;
	//print_r(count((get_records_sql($sql,$params,$limitfrom,$limitnum)))) ;
	return $result ;
} 

//print_r(test_get_records_sql($sql=null,$params=null, $limitfrom=0,$limitnum=null)) ."<br>"; 

function test_get_columns($table=null){
	if(!$table) {
		$table ='testblob' ;
	}
	$result = test_function('get_columns',array($table)) ;
	return $result ;
}

//print_r(test_get_columns($table=null))."<br>";

function test_execute($sql=null ,$params=null){
	if(!$sql){
		$sql = "INSERT INTO login(user,pass) values(?,?)";		
	}
	if(!$params){
		$params = array('Amit Patel','Enter') ;
	}
	$result = test_function('execute',array($sql,$params)) ;
	return $result ;
}

//print_r(test_execute($sql=null,$params=null))."<br>";

function test_count_records_sql($sql=null,$params=null){
	if(!$sql){
		$sql = "SELECT count(*) FROM login WHERE user=?" ;
	}
	if(!$params){
		$params = array('Amit Patel') ;
	}
	$result = test_function('count_records_sql',array($sql,$params));
	return $result ;
}

//print_r(test_count_records_sql($sql=null,$params=null))."<br>" ;

function test_get_field_sql($sql=null,$params=null){
	if(!$sql){
		$sql = "SELECT count(*) FROM login where user =?" ;		
	}
	if(!$params){
		$params = array('Amit Patel') ;
	}
	$result = test_function('get_field_sql',array($sql,$params));
	return $result ;
}

//print_r(test_get_field_sql($sql=null,$params=null))."<br>";

function test_count_records_select($table=null,$select=null,$params=null,$countitem=null){
	if(!$table){
		$table ='login' ;		
	}
	if(!$select){
		$select = 'user!=?';	
	}
	if(!$params){
		$params = array('') ;
	}
	if(!$countitem){
		//$countitem = "count(ALL user)" ;     // count(*) is default will show the all values from the column
		$countitem = "count(DISTINCT user)" ; // There are two count(ALL column_name) or count(DISTINCT column_name)
	}
	$result = test_function('count_records_select',array($table,$select,$params,$countitem)) ;
	return $result ;
}

print_r(test_count_records_select($table=null,$select=null,$params=null,$countitem=null))."<br>";

function test_update_record_raw($table=null,$params=null){
	if(!$table){
		$table = 'login' ;
	}
	if(!$params) {
		$params = array('id'=>'45','pass'=>'nowdefinite') ;
	}
	$result = test_function('update_record_raw',array($table,$params)) ;
	return $result ;
}

//print_r(test_update_record_raw($table=null,$params=null))."<br>";

function test_count_records($table=null,array $conditions=null){
	if(!$table){
		$table = 'login' ;
	}
	if(!$conditions){
		$conditions = array('user'=>'Amit Patel','pass'=>'n0wL!nux') ;	
	}
	$result = test_function('count_records',array($table,$conditions)) ;
	return $result ;
}

//print_r(test_count_records($table=null,$conditions=null))."<br>";

function test_insert_record_raw($table=null, array $params=null, $returnid=true, $bulk=false){
	if(!$table){
		$table = 'login' ;
	}
	if(!$params){
		$params = array('user'=>'Vikas Patel','pass'=>'Smily') ;
	}
	$result = test_function('insert_record_raw',array($table,$params,$returnid,$bulk)) ;
	//print_r(insert_record_raw($table, $params, $returnid=true, $bulk=false)) ;
	return $result ;
}

//print_r(test_insert_record_raw($table=null,$params=null,$returnid=true, $bulk=false)) ;

function test_where_clause($table , array $conditions=null) {
	if(!$table) {
		$table = 'login' ;
	}
	if(!$conditions){
		$conditions = array('user'=>'Amit Patel','pass'=>'n0wL') ;
	}
	$result = test_function('where_clause',array($table,$conditions)) ;
	//print_r(count(where_clause($table,$conditions)));
	return $result ;
}

//print_r(test_where_clause($table=null,$conditions=null)) ."<br>";

function test_where_clause_list($field, array $values=null) {
	$field ='user' ;
	if(!$values){
		$values = array('Amit','Sunil','Sachin') ;
	}
	
	$result = test_function('where_clause_list',array($field,$values)) ;
	//print_r(count(where_clause_list($field,$values)) );
	return $result ;
}

//print_r(test_where_clause_list($field,$values=null))."<br>";


// print_r(get_formname());
// echo "<br>" ;
// print_r(get_formdata()) ;
// echo "<br>" ;
?>
<?php 
/*Form with name login is created to test the 
get_formname and get_formdata    
<div id='div_form'>
	<form name='login' id='sign' action='<?php echo $_SERVER['PHP_SELF'] ;?>' method='post'>
		&nbsp;&nbsp;&nbsp;id:<input type='text' id='id' name='login-id' placeholder='must be ><?php 
			include_once('config.php');
			include_once('lib/orm.php');
			global $db;
			$sql = 'SELECT max(id) FROM `login` WHERE 1 ' ;
			$params = array(null) ;
			$result = $db->query($sql) ;
			while($row = $result->fetch_assoc()){
				if($row['max(id)']){
					echo $row['max(id)'] ;
				}
				else {
					echo int(0) ;
				}
			}
			?>'><br>
		user:<input type='text' id='user' name='login-user' placeholder='username'><br>
		pass:<input type='password' id='pass' name='login-pass' placeholder='password'><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='submit'>
	</form>
</div> */
?>    



