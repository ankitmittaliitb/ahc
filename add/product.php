<?php
include('../config.php'); 
include('../orm.php');
?>

<form id="add_product" name="add_product" method="post" action="product.php">
<input type="text" placeholder="product name" name="add_product-product"> <br>
<input type="text" placeholder="Quantity" name="add_product-qty">

<select name="add_product-type" form="add_product">
  <option value="tablets">Tablets</option>
  <option value="gms">Gms</option>
  <option value="vials">Vials</option>
  <option value="ml">ml</option>
  <option value="caps">Caps</option>
</select> <br>
<input type="submit" value="submit" name="add_product">
</form>

<?php
if(isset($_POST["add_product"])) {
	$data = get_formdata();
	$type = $data['add_product-type'];
	unset($data['add_product-type']);
	$data['add_product-qty'] = $data['add_product-qty'] . ' ' . $type;
	$product = convert_formdata_to_object($data, 'add_product');
	insert_record('products', $product);
	print_r($product);
}

?>