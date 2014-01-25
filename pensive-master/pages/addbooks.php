<?php
include('config.php'); 
include('lib/orm.php');
 ?>
<head>
<title>Bootstrap 101 Template</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="container">
<div style="margin-left:auto; margin-right:auto;"> <h1> Add new books to Library </h1>
<form name="uploadcsv" action=addbooks.php method=post enctype="multipart/form-data">
<div class="row">
<div class="col-md-4"><input type="file" name="uploadcsv-file" id="file" > </div>
<div class="col-md-4"><input type="submit" name="uploadcsv" value="Submit" class="btn btn-default"></div>
</div>
</form>
</div>

<?php
function get_books_data() {
	$books = array();
	
		if (($handle = fopen("temp/file.csv", "r")) !== FALSE) {
    		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        		$books[] = $data;
    		}
    		fclose($handle);
    		return $books;
    	} else {
    		return FALSE;
    	}
}


if(isset($_REQUEST['uploadcsv'])) {
    $uploaddir = '/var/www/library/temp/';
    $uploadfile = $uploaddir . 'file.csv';
    if (move_uploaded_file($_FILES['uploadcsv-file']['tmp_name'], $uploadfile)) {
        //success in uploading file
        //1. read the uploaded csv
		// $books = array();
	
		// if (($handle = fopen("temp/file.csv", "r")) !== FALSE) {
  //   		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
  //       		$books[] = $data;
  //   		}
  //   		fclose($handle);
			if ($books=get_books_data()) {
        //2. echo each and every line
    		$columns = $books[0]; ?> 
    		<table class="table table-condensed table-hover">
    		<form name="addbooks" action="addbooks.php" method="post">
    		<tr>
    		<?php
    		$header = $books[0];
    		for ($i=0; $i<count($header); $i++) {
    			echo '<th>' . $header[$i] . '</th>';
    		} ?>
    		<th> <input type="checkbox" id="all"> all </input> </th>s
    		</tr> 
    		<?php
    		for ($i = 1; $i < count($books); $i++) { ?>   			
    				<tr>
    					<?php
    						for ($j = 0; $j < 5; $j++) {
    							echo '<td>' . $books[$i][$j] . '</td>';
    						}
    				        //3. Ask to choose which ones to upload, with select all option
    					?>
    					<td><input type="checkbox" name="addbooks-<?php echo $books[$i][0]; ?>"> </td>
    				</tr>
    		<?php
    		}
    		?>
    		</table>
    		<input type="submit" name="addbooks" value="submit" class="btn btn-default">
    		</form>
    		<?php
    	} else {
	        echo "Possible file upload attack!\n";
    	}
    }
}

if(isset($_REQUEST['addbooks'])) {
	$data = get_formdata();
	$data_obj = convert_formdata_to_object($data, 'addbooks', 0 );	
	$books = get_books_data();
	$book_indexes = array_keys((array) ($data_obj));
	//$data = (array) $data_obj;
	$relavant_books = array();
    $keys = array('name', 'author', 'type', 'language');

	for($i=0; $i<count($book_indexes); $i++) {
		if(is_int($book_indexes[$i])) {
			$relavant_books[] = $books[$book_indexes[$i]];
			unset($relavant_books[$i]['0']);
			$relavant_books[$i] = array_combine($keys, array_values($relavant_books[$i]));
			if(empty($relavant_books[$i]['language'])) {
				$relavant_books[$i]['language'] = 'English';
			}
		}
	}
	$bookid = array();
	for ($i=0; $i<count($relavant_books); $i++) {
		$bookid[] = insert_record('books', $relavant_books[$i]);
	}
	echo '<h2>' . count($bookid) . ' books added </h2>';
	// echo "<pre>";
	// print_r($relavant_books);
	// echo "</pre>";
}
    		// echo "<pre>";
    		// print_r($books);
    		// echo "</pre>";
?>
</div>
<script src="bootstrap/assets/js/jquery.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript">
  $("input[type=checkbox]").not("#all").click(function(e) {
    if (!this.checked) { 
      $("#all")[0].checked = false; 
    }
  });

  $("#all").click(function(e) {
    var that = this;
    $("input[type=checkbox]").not("#all").each(function() {
       this.checked = that.checked; 
    });
  });
</script>
</body>
