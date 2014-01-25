<?

/**
*
* Connect to the database defined in config file.
* @return - mysqli link object, Flase if error.
*
*/
function connect_db() {
    global $CFG;
    global $db;
    $db = new mysqli($CFG->dbhost, $CFG->dbuser,$CFG->dbpass, $CFG->dbname);
    if ($db->connect_errno) {
        error_log("Failed to connect to MySQL: " . $db->connect_error);
        return FALSE;
    } else {
        return $db;
    }
}

/**
*
* Disconnects the database connection
* @param - mysqli link object, whose connection is to be closed
* @return - bool, False if not disconnected
*
*/
function disconnect_db($db) {
    return $db->close();
}

/* * * * * * * * get_formname * * * * *
*
* @return - Name of the form submitted
* all variable names submitted contain formname prior to -
* in short every form-item that is submitted is of the structure $_POST[formname-fieldname] = fieldvalue
*
*/
function get_formname() {
    if(empty($_REQUEST)) {
        return FALSE;
    } else {
        $keys = array_keys($_POST);
        $exploded_key = explode('-', $keys[0]);
        return $exploded_key[0];
    }
}

/**
*
* Used to catch values from all forms, both get and post,
* @return submitted data (By POST OR GET) or false in case of no data.
*
*/
function get_formdata(){
    if(empty($_REQUEST)) {
        return FALSE;
    } else {
        return fix_utf8($_REQUEST);
    }
}


/**
*
* fixes utf8 issues for anything that has to go into database
* reminds me of wordpress find and replace on live edupristine's site
* created havoc with most stuff, takes inputs as single string, array or object
* has recursion for objects and arrays
* @param - value accepted from form data or from outside apis
* @return - utf fixed value
*/
function fix_utf8($value) {
    if (is_null($value) or $value === '') {
        return $value;
    } else if (is_string($value)) {
        if ((string)(int)$value === $value) {
            // shortcut
            return $value;
        }
        return iconv('UTF-8', 'UTF-8//IGNORE', $value);

    } else if (is_array($value)) {
        foreach ($value as $k=>$v) {
            $value[$k] = fix_utf8($v);
        }
        return $value;
    } else if (is_object($value)) {
        $value = clone($value); // do not modify original
        foreach ($value as $k=>$v) {
            $value->$k = fix_utf8($v);
        }
        return $value;
    } else {
        // this is some other type, no utf-8 here
        return $value;
    }
}

/** * * * * * *Convert_formdata_to_object * * * * *
* Converts formdata or any associated array into an object
* to_strip contains the initial few characters which are to be removed
* from keys of array and are not supposed to go into the object name
*/


function convert_formdata_to_object($arr, $to_strip, $has_many=0) {
    //strip off second argument from keys of the array and then convert it into an
    //object or array of objects(in case third parameter is non zero)
    //not very efficient algo right now :-/
    //has many code has some error with digits > 9
    $to_strip = $to_strip . '-';
    $keys = array_keys($arr);
    $new_keys = array();
    foreach($keys as $key) {
        $new_keys[] = str_replace($to_strip, '', $key);
    }
    $new_arr = array_combine($new_keys,array_values($arr));
    if($has_many==1) {
        $final_arr = array();
        for($i = 0; $i < count($new_arr); $i++) {
            $index = substr($new_keys[$i], -1);
            if(is_numeric($index)) {
            $final_key = rtrim($new_keys[$i], $index);
            if(isset($final_arr[$index])) {
            $final_arr[$index]->$final_key = $new_arr[$new_keys[$i]];
            } else {
                $final_arr[$index] = new stdClass;
                $final_arr[$index]->$final_key = $new_arr[$new_keys[$i]];
            }
            } else {
                //either this is n or bad input
                $final_arr[$index] = $new_arr[$new_keys[$i]];
            }
        }
        //should return array of objects
        return $final_arr;
    } elseif($has_many == 0) {
        //should return an object
        return (object) $new_arr;
    } else {
        return 'wrong value of has_many';
    }
}   


/**
* Insert a record into a table and return the "id" field if required.
*
* Some conversions and safety checks are carried out. Lobs are supported.
* If the return ID isn't required, then this just reports success as true/false.
* $data is an object containing needed data
* @param string $table The database table to be inserted into
* @param object $data A data object with values for one or more fields in the record
* @param bool $returnid Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
* @return bool|int true or new id
*/
function insert_record($table, $dataobject, $returnid=true, $bulk=false) {
    $dataobject = (array)$dataobject;
    $columns = get_columns($table);
    $cleaned = array();
    foreach ($dataobject as $field=>$value) {
        if ($field === 'id') {
            continue;
            //ignore if id field is given in the dataobject
        }
        if (!in_array($field, $columns, TRUE)) {
            continue;
        //ignore if this field is not there in column
        }
        $column = $columns[array_search($field, $columns)];
        $cleaned[$field] = normalise_values($column, $value);
    }
    if($index = array_search('date_created', $columns)) {
        //[PENDING] - date not showing up in db
        $cleaned['date_created'] = time();
        $cleaned['date_modified'] = $cleaned['date_created'];
    }
    return insert_record_raw($table, $cleaned, $returnid, $bulk);
}

function insert_record_raw($table, $params, $returnid, $bulk) {
    global $db ;
    if(!array($params)) {
        $params = (array) $params;
    }
    if(empty($params)) {
        //log error to no fields found
        return -1;
    }
    $columns = implode(',', array_keys($params));
    if(count($params)==1){
        $values = implode(',', $params) ;    
        $values = normalise_values($columns,$values) ;
    }
    else {
        $values = array() ;
        $values = $params ;
        $values = normalise_values($columns,$values) ;   
    }
    $sql = "INSERT INTO $table ($columns) VALUES($values)";   
    $result = $db->query($sql) ;
    $id =  mysqli_insert_id($db) ;
    return $id;
}

/**
* Normalise values based in RDBMS dependencies (booleans, LOBs...)
* Pending (all that is done in moodle, after get_columns is finished
* @param $value to normalise and $column of the column
*/
function normalise_values($columns, $values) {
    if (is_bool($values)) { // Always, convert boolean to int
        return (int)$values;
    }
    if (is_string($values)) {
        return "'$values'"; //Add singlequotes around the string
    }
    if(is_array($values)){
        $string ="" ;
        foreach ($values as $value) {
            $string .= "'$value'";
        }
        $string = str_replace("''", "','", $string) ;
        return $string ;
    }
}

/**
*
* returns array of columns in the given table and a key 'n'
* whose value is total number of columns in the table.
* @param $table is the table name of which every column will be shown 
*/
function get_columns($table) {
    global $db ;
    $sql = "SHOW COLUMNS FROM $table";
    $result = $db->query($sql);
    $column = array();
    $n = 0;
    while($row = $result->fetch_assoc()){
        $column[] = $row['Field'];
        $n = $n + 1 ;
    }
    $column['n'] = $n;
    return $column;
}


/**
*
*
*
*/

function insert_record_from_postform($table) {
    $form_data_raw = get_postdata();
    $form_name = get_formname();
    $form_data_as_object = convert_formdata_to_object($form_data_raw, $form_name);
    $id = insert_record($table, $form_data_as_object);
    return $id;
}

/**
* Count the records in a table where all the given conditions met.
* @param string $table The table to query.
* @param array $conditions optional array $fieldname=>requestedvalue with AND in between
* @return int The count of records returned from the specified criteria.
*/
function count_records($table, array $conditions=null) {
    list($select, $params) = where_clause($table, $conditions);
    return count_records_select($table, $select, $params);
}

/**
* Count the records in a table which match a particular WHERE clause.
* @param string $table The database table to be checked against.
* @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
* @param array $params array of sql parameters
* @param string $countitem The count string to be used in the SQL call. Default is COUNT('x').
* @return int The count of records returned from the specified criteria.
*/
function count_records_select($table, $select, array $params=null, $countitem="COUNT('x')") {
    if ($select) {
        $select = "WHERE $select" ;
    }
    return count_records_sql("SELECT $countitem FROM " . $table . " $select", $params);
}

/**
* Get the result of a SQL SELECT COUNT(...) query.
*
* Given a query that counts rows, return that count. (In fact,
* given any query, return the first field of the first record
* returned. However, this method should only be used for the
* intended purpose.) If an error occurs, 0 is returned.
*
* @param string $sql The SQL string you wish to be executed.
* @param array $params array of sql parameters
* @return int the count
*/
function count_records_sql($sql, array $params=null) {
    if ($count = get_field_sql($sql, $params)) {
        return $count;
    } else {
        return 0;
    }
}

/**
* Test whether a record exists in a table where all the given conditions met.
*
* @param string $table The table to check.
* @param array $conditions optional array $fieldname=>requestedvalue with AND in between
* @return bool true if a matching record exists, else false.
*/
function record_exists($table, array $conditions) {
    list($select, $params) = where_clause($table, $conditions);
    return record_exists_select($table, $select, $params);
}

/**
* Test whether any records exists in a table which match a particular WHERE clause.
*
* @param string $table The database table to be checked against.
* @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
* @param array $params array of sql parameters
* @return bool true if a matching record exists, else false.
*/

function record_exists_select($table, $select, array $params=null) {
    if ($select) {
        $select = "WHERE $select";
    }
    return record_exists_sql("SELECT 'x' FROM " . $table . " $select", $params);
}

/**
* Test whether a SQL SELECT statement returns any records.
* This function returns true if the SQL statement executes
* without any errors and returns at least one record.
* @param string $sql The SQL statement to execute.
* @param array $params array of sql parameters
* @return bool true if the SQL executes without errors and returns at least one record.
*/
function record_exists_sql($sql, array $params=null) {
    //add logic here using get_field_sql :D
    
    return $return;

}
/**
* Get a single field value (first field) using a SQL statement.
* Will be used for count, record_exists etc purposes where only
* one field is to be queried
*
* @param string $sql The SQL query returning one row with one column
* @param array $params array of sql parameters
* @return mixed the specified value, false if not found
*/
function get_field_sql($sql, array $params=null) {
    if (!$record = get_record_sql($sql, $params)) {
        return false;
    }
    $record = (array)$record;
    return reset($record); // first column
}

/**
* Get a single database record as an object using a SQL statement.
* The SQL statement should normally only return one record.
* It is recommended to use get_records_sql() if more matches possible!
* @param string $sql The SQL string you wish to be executed, should normally only return one record.
* @param array $params array of sql parameters
* @return mixed a fieldset object containing the first matching record, false or exception if error not found depending on mode
*/
function get_record_sql($sql, array $params=null) {
    if (!$records = get_records_sql($sql, $params, 0, 1)) {
        // not found
        return false;
    }
    $return = reset($records);
    return $return;
}
        
/**
* Get a number of records as an array of objects using a SQL statement.
* @param string $sql the SQL select query to execute. The first column of this SELECT statement
* must be a unique value (usually the 'id' field), as it will be used as the key of the
* returned array.
* @param array $params array of sql parameters
* @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
* @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
* @return array of objects indexed by first column ***
*/
 
function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {
    global $db ;
    $limitfrom = (int)$limitfrom;
    $limitnum = (int)$limitnum;
    //Negative values dont make sense here
    $limitfrom = ($limitfrom < 0) ? 0 : $limitfrom;
    $limitnum = ($limitnum < 0) ? 0 : $limitnum;
    if ($limitfrom or $limitnum) {
        if ($limitnum < 1) {
            //some large number
            $limitnum = "18446744073709551615";
        }
        $sql .= " LIMIT $limitfrom, $limitnum";
    }
    // [todo] check if number of question marks and number of params are
    // same. a new fix or normalise function maybe
    
    //[Todo] Mysql escape string on params..
    $real_sql = normalise_params($sql, $params);
    // [Todo] Log every query
    $result = $db->query($real_sql);
    $return = array();
    while($row = $result->fetch_assoc()) {
        $row = array_change_key_case($row, CASE_LOWER);
        $id = reset($row);
        if (isset($return[$id])) {
                $colname = key($row);
                error_log("Did you remember to make the first column something unique in your call to
                get_records? Duplicate value '$id' found in column '$colname'");
        }
        $return[$id] = (object)$row;
    }
    $result->close();
    return $return;
}

/**
* Returns the normalised values of the array $params 
* It replaces the ? with the values of $params and make sql to execute .
* @param $sql contains the query to execute 
* @param $params array of $sql parameters 
*
*/

function normalise_params($sql, array $params=null) {
    global $db ; 
    if (empty($params)) {
        return $sql;
    }
    if(substr_count($sql, '?')==count($params)) {
        /// ok, we have verified sql statement with ? and correct number of params
        $parts = explode('?', $sql);
        $return = array_shift($parts);
        foreach ($params as $param) {
            if (is_bool($param)) {
                $return .= (int)$param;
            } else if (is_null($param)) {
                $return .= 'NULL';
            } else if (is_numeric($param)) {
                $return .= "'".$param."'"; // we have to always use strings because mysql is using weird automatic int casting
            } else if (is_float($param)) {
                $return .= $param;
            } else {
                $param = $db->real_escape_string($param);
                $return .= "'$param'";
            }
            $return .= array_shift($parts);
        }
        return $return;
    } 
}


/**
* Delete the records from a table where all the given conditions met.
* If conditions not specified, table is truncated.
*
* @param string $table the table to delete from.
* @param array $conditions optional array $fieldname=>requestedvalue with AND in between
* @return bool true.
*/
function delete_records($table, array $conditions=null) {
    // truncate is drop/create (DDL), not transactional safe,
    // so we don't use the shortcut within them. MDL-29198
    if (is_null($conditions) && empty($this->transactions)) {
        return execute("TRUNCATE TABLE {".$table."}");
    }
    list($select, $params) = where_clause($table, $conditions);
    return delete_records_select($table, $select, $params);
}


/**
* Delete one or more records from a table which match a particular WHERE clause.
*
* @param string $table The database table to be checked against.
* @param string $select A fragment of SQL to be used in a where clause in the SQL call (used to define the selection criteria).
* @param array $params array of sql parameters
* @return bool true
*/
function delete_records_select($table, $select, array $params=null) {
    global $db ;
    if ($select) {
        $select = "WHERE $select";
    }
    $sql = "DELETE FROM $table $select";
    $rawsql = normalise_params($sql, $params);
    $result = $db->query($rawsql);
    return true;
}

/**
* Delete the records from a table where one field match one list of values.
*
* @param string $table the table to delete from.
* @param string $field The field to search
* @param array $values array of values
* @return bool true.
*/
function delete_records_list($table, $field, array $values) {
    list($select, $params) = where_clause_list($field, $values);
    if (empty($select)) {
        // nothing to delete
        return true;
    }
    return delete_records_select($table, $select, $params);
}

/*
*
* Returns SQL WHERE conditions.
* converts all verb_object functions into verb_object_select functions
* conditions is input as array is converted into a WHERE string with keys & "?"
* and an array of values
*/
function where_clause($table, array $conditions=null) {
    // Null conditions is converted into an empty array
    $conditions = is_null($conditions) ? array() : $conditions;
    // if conditions is an empty array, return an empty string and empty array of params
    // this may look wierd but an input maybe a no array, a null or an empty array
    // So all cases are handled properly
    if (empty($conditions)) {
        return array('', array());
    }
    //initialize where as an array only, will implode later to convert it into string
    $where = array();
    $params = array();
    foreach ($conditions as $key=>$value) {
        if (is_int($key)) {
            return -1;
            //raise error
        }
        if (is_null($value)) {
            // take care of null value
            $where[] = "$key IS NULL";
        } else {
            $where[] = "$key = ?";
            $params[] = $value;
        }
    }
    $where = implode(" AND ", $where);
    return array($where, $params);
}


/**
* Returns SQL WHERE conditions for the ..._list methods.
*
* @param string $field the name of a field.
* @param array $values the values field might take.
* @return array sql part and params
*/
function where_clause_list($field, array $values) {
    $params = array();
    $select = array();
    $values = (array)$values;
    foreach ($values as $value) {
        if (is_bool($value)) {
            $value = (int)$value;
        }
        if (is_null($value)) {
            $select[] = "$field IS NULL";
        } else {
            $select[] = "$field = ?";
            $params[] = $value;
        }
    }
    $select = implode(" OR ", $select);
    return array($select, $params);
}


/**
* Update record in database, as fast as possible, no safety checks, lobs not supported.
* @param string $table name
* @param mixed $params data record as object or array
* @param bool true means repeated updates expected
* @return bool true
*/

function update_record_raw($table, $params, $bulk=false) {
    global $db ;
    $params = (array)$params;
    if (!isset($params['id'])) {
        error_log("No id in update query");
    }
    $id = $params['id'];
    unset($params['id']);
    if (empty($params)) {
        error_log("No parameters to update");
    }
    $sets = array();
    foreach ($params as $field=>$value) {
        $sets[] = "$field = ?";
    }
    $params[] = $id; // last ? in WHERE condition
    $sets = implode(',', $sets);
    $sql = "UPDATE $table SET $sets WHERE id=?";
    $rawsql = normalise_params($sql, $params);
    $result = $db->query($rawsql);
    return true;
}

/**
* Update a record in a table
*
* $dataobject is an object containing needed data
* Relies on $dataobject having a variable "id" to
* specify the record to update
*
* @param string $table The database table to be checked against.
* @param object $dataobject An object with contents equal to fieldname=>fieldvalue. Must have an entry for 'id' to map to the table specified.
* @param bool true means repeated updates expected
* @return bool true
*/
function update_record($table, $dataobject, $bulk=false) {
    $dataobject = (array)$dataobject;
    $columns = get_columns($table);
    $cleaned = array();
    foreach ($dataobject as $field=>$value) {
        if (!isset($columns[$field])) {
            continue;
        }
        $column = $columns[$field];
        $cleaned[$field] = normalise_values($column, $value);
    }
    return update_record_raw($table, $cleaned, $bulk);
}

/**
* Execute general sql query. Should be used only when no other method suitable.
* Do NOT use this to make changes in db structure, use database_manager methods instead!
* @param string $sql query
* @param array $params query parameters
* @return bool true
*/
function execute($sql, array $params=null) {
    global $db ;
    if (strpos($sql, ';') !== false) {
        error_log("Multiple statements or bad sql containing ;");
    }
    $rawsql = normalise_params($sql, $params);
    $result = $db->query($rawsql);
    return $result;

}

/**
*
* function to test functions
* @param - Name of the function to be tested
* @param - List (array) of inputs to be used with the function, even if there is one input it has to be put into an array
* @param - List of error values, the test will fail if returned value from first parameter function is one of these, else
* the test is passed. Default error value is FALSE
* @return - Returned is the string which either contains test passed or test failed, along with the return values in <pre>
*/
function test_function($function_name, $input=null, $errors=null) {
//[Pending] - Echo test passed output false / whatever is outcome and output failed when function breaks.
//Give option of giving inputs to funcitons also
    if(function_exists($function_name)) {
        if($input==null) {
            $input = array();
        }
        if($errors==null) {
            $errors = FALSE;
        }
        if(empty($input)) {
            $output = $function_name();
        } else {
            $output = call_user_func_array($function_name, $input);
        }

        if(!in_array($output, $errors)) {
            $result = 'Test Passed';
        } else {
            $result = 'Test Failed';
        }

        $result .= '<br>Outuput is :<pre>';
        $result .= json_encode($output);
        $result .= '</pre>';
        return $result;
    } else {
        return "Bad Function Name, Doesnt exist";
    }
}

?>
