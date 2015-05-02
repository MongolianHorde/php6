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
	
	// Database credentials
	define('DATABASEADDRESS','localhost');  // Host name
	define('DATABASEUSER', 'root');         // User name
	define('DATABASEPASS', '');             // Database password
	define('DATABASENAME', 'CS368_120012'); // Database name

	// Connect to the database
	@$database = new mysqli(DATABASEADDRESS, DATABASEUSER, DATABASEPASS, DATABASENAME);
	
	// Grab username
	@$user = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
	
	// Insert a new item into the customer order and order line tables
	if(@$_GET['cart_action'] == 'Add')
	{
		@$productId = $_GET['p_id'];
		$orderExists = false;
		$existingId = 0;
		
		$quantityUpdated = false;
		
		$orderId = null;
		
		// Check if an order exists by a specific username
		$selectOrderId = "select order_id from customer_order where order_username = ? and order_status = 'P'";
		$selectOrderIdStatement = $database->prepare($selectOrderId);
		$selectOrderIdStatement->bind_param("s", $user);
		$selectOrderIdStatement->bind_result($orderId);
		$selectOrderIdStatement->execute();
		$selectOrderIdStatement->fetch();
		$selectOrderIdStatement->close();
		
		// An order exists
		if($orderId != null)
		{
			$orderExists = true;
			$existingId = $orderId;
		}
		
		// If order exists, grab that order id and put another order line
		if($orderExists)
		{
			$orderLineId = null;
		 //echo '<h1> Product id if order exists: ' . $productId . '</h1>';
			$selectOrderLine = "select order_line_id, product_id, order_line_quantity from order_line where product_id = ?";
			$selectOrderLineStatement = $database->prepare($selectOrderLine);
			$selectOrderLineStatement->bind_param("s", $productId);
			$selectOrderLineStatement->bind_result($orderLineId, $productId, $orderLineQuantity);
			$selectOrderLineStatement->execute();
			$selectOrderLineStatement->fetch();
			$selectOrderLineStatement->close();
			
			if($orderLineId != null)
			{
				// Item already in cart so add 1 to total
				$orderLineQuantity += 1;
				$updateOrderLine = "update order_line set order_line_quantity = ? where order_line_id = ?";
				$updateOrderLineStatement = $database->prepare($updateOrderLine);
				$updateOrderLineStatement->bind_param("ss", $orderLineQuantity, $orderLineId);
				$updateOrderLineStatement->execute();
				$updateOrderLineStatement->close();
				$quantityUpdated = true;
			}

			if(!$quantityUpdated)
			{
				$orderLineQuery = "select count(*) from order_line";
				$orderLineStatement = $database->prepare($orderLineQuery);
				$orderLineStatement->execute();
				$orderLineStatement->bind_result($orderLineStatementResult);
				if($orderLineStatementResult == null)
					$orderLineStatementResult = 0;
				while($orderLineStatement->fetch())
				{
					$newOrderLineId = $orderLineStatementResult + 1;
				}
				$orderLineStatement->close();
				
				/*
				$new_order_line_id = $order_line_result->num_rows + 1;
				$order_line_result->free();*/
				@$productId = $_GET["p_id"];
				echo '<h1>inside add: orderLineId: ' . $newOrderLineId . "orderId: " .$existingId. " productId: ". $productId;
			
				$insertOrderLineQuery = "insert into order_line(order_line_id, order_id, product_id, order_line_quantity)
														 values(?, ?, ?, 1)";
				$insertOrderLineStatement = $database->prepare($insertOrderLineQuery);
				$insertOrderLineStatement->bind_param("sss", $newOrderLineId, $existingId, $productId);
				$insertOrderLineStatement->execute();
				$insertOrderLineStatement->close();
			}
		}
		// if it doesnt exist, make a new order
		else
		{
			// Get the new id number
			$orderIdQuery = "select count(*) from customer_order";
			$orderIdStatement = $database->prepare($orderIdQuery);
			$orderIdStatement->execute();
			$orderIdStatement->bind_result($orderIdStatementResult);
			while($orderIdStatement->fetch())
			{
				$newOrderId = $orderIdStatementResult + 1;
			}
			$orderIdStatement->close();
			
			/*$newOrderId = $ordersResult->num_rows + 1;
			$ordersResult->free();
			*/
			$status = 'P';
			
			@$user = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
			//$username = $_SERVER['AUTH_USER'];
			
			// insert into order table
			$insertOrderQuery = "insert into customer_order(order_id, order_status, order_username, order_date)
											 values(?, ?, ?, curdate())";
			$newOrder = $database->prepare($insertOrderQuery);
			$newOrder->bind_param("sss", $newOrderId, $status, $user);
			$newOrder->execute();
			$newOrder->close();
			
			// insert into order line
			$orderLineQuery = "select count(*) from order_line";
			$orderLineStatement = $database->prepare($orderLineQuery);
			$orderLineStatement->execute();
			$orderLineStatement->bind_result($orderLineStatementResult);
			while($orderLineStatement->fetch())
			{
				$newOrderLineId = $orderLineStatementResult + 1;
			}
			$orderLineStatement->close();
			/*
			$ol_id_query = "select * from order_line";
			$order_line_result = $database->query($ol_id_query);
			$new_order_line_id = $order_line_result->num_rows + 1;
			$order_line_result->free(); */
			
			$insertOrderLineQuery = "insert into order_line(order_line_id, order_id, product_id, order_line_quantity)
													 values(?, ?, ?, 1)";
			$insertOrderLineStatement = $database->prepare($insertOrderLineQuery);
			$insertOrderLineStatement->bind_param("sss", $newOrderLineId, $newOrderId, $productId);
			$insertOrderLineStatement->execute();
			$insertOrderLineStatement->close();
		}
	}
	// Update the quantity ordered
	if(@$_GET['cart_action'] == 'update')
	{
		$updateQuantity = $_GET["quantity"];
		$orderLineId = $_GET["ol_id"];
		$orderId = $_GET["o_id"];
		
		echo '<br />' . $updateQuantity . " " . $orderLineId . " " . $orderId;
		
		if(!preg_match("#^[[:digit:]]$#", $updateQuantity))
		{
			echo "Quantity must be numeric.";
		}
		else if($updateQuantity < 0)
		{
			echo "Quantity must be positive.";
		}	
		else
		{
			$updateQuantityQuery = "update order_line
							set order_line_quantity = ?
							where order_id = ? and order_line_id = ?";
			$updateQuantityStatement = $database->prepare($updateQuantityQuery);
			$updateQuantityStatement->bind_param("sss", $updateQuantity, $orderId, $orderLineId);
			$updateQuantityStatement->execute();
			$updateQuantityStatement->close();
		}
	}
	// Delete an item
	if(@$_GET['cart_action'] == 'delete')
	{
		$orderLineId = $_GET["ol_id"];
		$orderId = $_GET["o_id"];
		echo '<br />'  . $orderId . " " . $orderLineId;
		
		$deleteQuery = "delete from order_line
							where order_id = ? and order_line_id = ?";
		$deleteStatement = $database->prepare($deleteQuery);
		$deleteStatement->bind_param("ss", $orderId, $orderLineId);
		$deleteStatement->execute();
		$deleteStatement->close();
	}
	// Complete the order
	if(@$_GET['cart_action'] == 'checkout')
	{
		$orderId = $_GET["o_id"];
			echo $orderId;
		$completeOrder = "update customer_order
						set order_status = 'C'
						where order_id = ?";
		$completeOrderStatement = $database->prepare($completeOrder);
		$completeOrderStatement->bind_param("s", $orderId);
		$completeOrderStatement->execute();
		$completeOrderStatement->close();
	}
	
	
	?>
<body>
   <header>
	<img class="titlePic" src="images/toolTime.jpg" alt="text" />
		<h1>Cart Design</h1>
		<nav class="navClass">
			<a href="program6.php">Products</a>
			<a href="cart.php"> My Cart</a>		
		</nav>	
	</header>
	<h2>My Cart</h2>
	<table>
		<tr>
			<th></th>
			<th>Product</th>
			<th>Price</th>
			<th>Quantity</th>
		</tr>
		<?php
		// List all the products
		$listProductsQuery = "select product_id, product_name, product_price, product_picture, order_line_quantity, order_line_id, order_id from product join order_line using (product_id) join customer_order using (order_id) where order_username = ? and order_status = 'P'";
		$listProductsStatement = $database->prepare($listProductsQuery);
		$listProductsStatement->bind_param("s", $user);
		$listProductsStatement->bind_result($pid, $pname, $pprice, $ppic, $quantity, $olid, $oid);
		$listProductsStatement->execute();
		while($listProductsStatement->fetch())
		{
				//$orderIdCheckout = $oid;
			echo'
			<tr>
				<td><img class="cartPhoto" src="images/'.$ppic.'" alt="Text here" /></td>
				<td>'.$pname.'</td>
				<td>'."$".$pprice.'</td>
				<td>
					<form name="item1_cart_form" id="item1_cart_form" action="cart.php" method="get">
						<input type="number" name="quantity" value="'.$quantity.'"/>
						<br/>
						<button type="submit" name="cart_action" value="update">Update</button>
						<input type="hidden" name="ol_id" value="'.$olid.'"/>
						<input type="hidden" name="o_id" value="'.$oid.'"/>
						<br/>
						
					</form>
					<a href="cart.php?cart_action=delete&amp;o_id='.$oid.'&amp;ol_id='.$olid.'">Delete Item</a>
				</td>
			</tr>';
		}
		$listProductsStatement->close();	
		?>
		
		<tr>
			<th colspan="3">Total Price = 
			<?php 
			$totalQuery = "select product_price * order_line_quantity
									from product
									join order_line
									using(product_id)
									join customer_order
									using(order_id)
									where order_username = ? and order_status = 'P'";
			$totalStatement = $database->prepare($totalQuery);
			$totalStatement->bind_param("s", $user);
			$totalStatement->bind_result($total);
			$totalStatement->execute();
			$totalPrice = 0;
			
			
				while($totalStatement->fetch())
				{
					
					$totalPrice += $total;
				}
			
			$totalStatement->close();
			
				// We've checked out
			if($total == null)
			{
				$totalPrice = 0;
				$checkoutTotal = "select product_price * order_line_quantity
										from product
										join order_line
										using(product_id)
										join customer_order
										using(order_id)
										where order_username = ? and order_status = 'C' and order_id = ?";
				$checkoutStatement = $database->prepare($checkoutTotal);
				$checkoutStatement->bind_param("ss", $user, $orderId);
				$checkoutStatement->bind_result($total2);
				$checkoutStatement->execute();
				while($checkoutStatement->fetch())
				{
					$totalPrice += $total2; 
				}
				$checkoutStatement->close();
			}
			$database->close();
			
		
				
			echo "$". $totalPrice;
			?></th>
			<th>
				<?php echo'<a href="cart.php?cart_action=checkout&amp;o_id='.$orderId.'">Check out</a>'; ?>
			</th>
		</tr>
	</table> 
 
</body>
</html>