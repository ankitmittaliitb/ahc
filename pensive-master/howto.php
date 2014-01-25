<?php
//file for testing functions
include_once('config.php');
include_once('lib/orm.php');
global $db ;
connect_db();

$sql = "SELECT * FROM login where user=? AND pass=?" ;
$params = array('Amit Patel','Smily') ;
$limitfrom = 0 ;
$limitnum =10;
print_r(get_records_sql($sql,$params, $limitfrom, $limitnum));



























?>