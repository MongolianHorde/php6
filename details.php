<!DOCTYPE HTML> 

<html>
<head>
   <meta name="author" content="Mr Sparks" />
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
   <link href="style06.css" rel="stylesheet" type="text/css" />
   <title>Database Project 2</title>
   </head>
	
	<?php
	// Author: David Hughen
	// CS 368: Advanced Web Programming
	// Date: 4/17/2015
	// This page displays details for the product selected.
	session_start();
	// Database credentials
	define('DATABASEADDRESS','localhost');  // Host name
	define('DATABASEUSER', 'root');         // User name
	define('DATABASEPASS', '');             // Database password
	define('DATABASENAME', 'CS368_120012'); // Database name

	// Connect to the database
	@$database = new mysqli(DATABASEADDRESS, DATABASEUSER, DATABASEPASS, DATABASENAME);
	
	$listProductDetailsQuery = "select product_id, product_description, product_name, product_picture, product_price, product_year from product
											where product_id = ?";

	$listProductDetailsStatement = $database->prepare($listProductDetailsQuery);
	
	
	?>
<body>
   <header>
	<img class="titlePic" src="images/toolTime.jpg" alt="text" />
		<h1>Cart Design</h1>
		<nav class="navClass">
			<a href="program6.php">Saws</a>
			<a href="cart.php"> My Cart</a>		
		</nav>	
	</header>	
	<table>
	<?php
	@$productId = $_GET['p_id'];
	$listProductDetailsStatement->bind_result($pid, $pdesc, $pname, $ppic, $pprice, $pyear);
	$listProductDetailsStatement->bind_param("s", $productId);
	$listProductDetailsStatement->execute();
	while($listProductDetailsStatement->fetch())
	{
	
	echo'
		
		 <h2>'.$pname.'</h2>
		<h3 class="price">Cost '. "$".$pprice.'</h3>
		<img class="cartPhoto" src="images/'.$ppic.'" alt="Text here" />
		<h3>Description</h3>
		<p class="desc">'.$pdesc.'
			<br /><br />
			<a href="cart.php?cart_action=Add&amp;p_id='.$pid.'">Add to Cart</a>
		</p>';

	}	
	$listProductDetailsStatement->close();
	
	$database->close();
	?>	
	</table>
</body>
</html>