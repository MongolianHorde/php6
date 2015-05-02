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
	// This page displays all products in the system.

	// Database credentials
	define('DATABASEADDRESS','localhost');  // Host name
	define('DATABASEUSER', 'root');         // User name
	define('DATABASEPASS', '');             // Database password
	define('DATABASENAME', 'CS368_120012'); // Database name

	// Connect to the database
	@$database = new mysqli(DATABASEADDRESS, DATABASEUSER, DATABASEPASS, DATABASENAME);
		
$listProductsQuery = "SELECT product_id, product_name, product_picture, product_price
								from product";

$listProductsStatement = $database->prepare($listProductsQuery);				

	?>
<body>
   <header>
	<img class="titlePic" src="images/toolTime.jpg" alt="text" />	
		<h1>Does everyone know what time it is?...Tool Time!!!</h1>
		
		<nav class="navClass">
			<a href="program6.php">Saws</a>
			<a href="cart.php"> My Cart</a>
		</nav>
	
	</header>
	<h2>Binford 6100 Saws</h2>
	<table>
		<tr>
			<th>Image</th>
			<th>Product</th>
			<th>Price</th>
			<th>Details</th>
			
		</tr><tr>
		<?php
			$listProductsStatement->bind_result($pid, $pname, $ppic, $pprice);
			$listProductsStatement->execute();
			while($listProductsStatement->fetch())
			{
				echo'
				<td><img class="cartPhoto" src="images/'.$ppic.'" alt="Text here" /></td>
				<td>'.$pname.'</td>
				<td>'." $".$pprice.'</td>
				<td>
					<a href="details.php?p_id='.$pid.'">See Details</a>
				</td>
			</tr>';
			}
			$listProductsStatement->close();
			
			
			
			$database->close();
			
		?>	
		</tr>
	</table> 
 
</body>
</html>	
  