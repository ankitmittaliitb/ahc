pensive
=======

===========  Connecting the database  ===================> <br/>
include_once('config.php'); <br/>
include_once('lib/orm.php'); <br/>
global $db ; <br/>
connect_db(); <br/>
You can configure database name in config.php <br/>


==============  The Functions  =============================> <br/>
Getting columns of the table <br/>
$columns = get_columns($table) ;  <br/>
This will return columns of table in an array 


------------------------------------------------------------

Executing a sql <br/>
// After successful execution , it will return the true . Else false . <br/>
// The ? must be equal to the number of values in params  <br/>
$sql = "INSERT INTO login(user,pass) values(?,?)";	<br/>
$params = array('Amit Patel','Enter') ;<br/>
execute($sql,$params) ;<br/>
<br/>
------------------------------------------------------------<br/>
Inserting new record into table <br/>
// This will return the id of the inserted row <br/>
// Pass the value in the associated array like this ('field'=>'value') <br/>
$table = 'login' ;<br/>
$params = array('pass'=>'Smily') ;<br/>
insert_record_raw($table, $params, $returnid=true, $bulk=false) ;<br/>
<br/>
------------------------------------------------------------<br/>
Getting the records upto which you want at a time <br/>
// This will return the existing records upto defined limit <br/>
// Pass the where condition like this . There must be same number of ? as the params has values.<br/>
// It will be normalised according to their positions in array params.<br/>
// After this function it will show the result upto 10 rows <br/>
$sql = "SELECT * FROM login where user=? AND pass=?" ;<br/>
$params = array('Amit Patel','Smily') ;<br/>
$limitfrom = 0 ;<br/>
$limitnum =10;<br/>
get_records_sql($sql,$params, $limitfrom, $limitnum);<br/>
<br/>
------------------------------------------------------------ <br/>
Updating a record <br/>
// This will return true after updating the record.<br/>
// Which values you want to update , you can .<br/>
$table = 'login' ; <br/>
$params = array('id'=>'45','user'=>'ubunty',pass'=>'nowdefinite') ;<br/>
update_record_raw($table,$params) ;<br/>
<br/>
-----------------------------------------------------------<br/>
Counting the all records for given condition<br/>
// This will return the number of records exists <br/>
// You can add some more conditions depends upon your requirement <br/>
$sql = "SELECT count(*) FROM login WHERE user=?" ;<br/>
$params = array('Anil') ;<br/>
count_records_sql($sql,$params) ;<br/>
<br/>
Counting the records where select condtion is given <br/>
// This will return the count where condition matches <br/>
// You can pass count(*) for all values or <br/>
// count(DISTINCT column_name) for distinct values of that column  <br/>
$table ='login' ; <br/>
$select = 'user=?';	<br/>
$params = array('Amit Patel') ; <br/>
$countitem = "count(*)" ; <br/>
count_records_select($table,$select,$params,$countitem) ; <br/>
----------------------------------------------------------<br/>
Deleting records <br/>
// This will return the true after deleting the records <br/>
$table = 'login' ;<br/>
$conditions = array('user'=>'Vikas Patel','pass'=>'Smily');<br/>
delete_records($table , $conditions) ;<br/>
<br/>
Deleting records by select <br/>
// This will return the true also<br/>
// The select condtion should be matched by the passing values in params <br/>
$table ='login' ;<br/>
$select = 'id>? AND user=? AND pass=?' ;<br/>
$params = array(5, 'Amit Patel','Enter') ;<br/>
<br/>
Deleting records by the list of values of a field <br/>
// This will again return true .<br/>
$table ='login' ;<br/>
$field = 'user' ;<br/>
$values = array('','Amit Patel','Sunil Patel');<br/>
delete_records_list($table ,$field , $values) ;<br/>
<br/>



















































