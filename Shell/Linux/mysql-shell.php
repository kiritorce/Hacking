<?php
	
	//Me mashing my keyboard, aka uncrackable password.
	//Don't want to accidentally leave this lying around unsecure.
	$password = 'BI.d,04d3id0X<*($#b*d3lgxiX(OEuxi9p,i9ib4bai8(XO>UeoknX<oetbx*X>I<BShbibRLRX980{I$0d';
	
	session_start();
	
	if ( isset( $_POST['clear'] ) AND $_POST['clear'] == 'clear' ) {
		clear_history();
	}
	
	if ( ! isset( $_SESSION['mysql_shell_queries'] ) ) {
		$_SESSION['mysql_shell_queries'] = array();
		$_SESSION['mysql_shell_query_responses'] = array();
	}
	
	$previous_queries = '';
	
	if ( isset( $_POST['query'] ) ) {
		$query = $_POST['query'];
		if ( ! isset( $_SESSION['mysql_shell_logged_in'] ) ) {
			if ( $query == $password ) {
				$_SESSION['mysql_shell_logged_in'] = TRUE;
				$response = array( 'Welcome!!' );
			} else {
				$response = array( 'Incorrect Password' );
			}
			array_push( $_SESSION['mysql_shell_queries'], 'Password: ******' );
			array_push( $_SESSION['mysql_shell_query_responses'], $response );
		} else {
			if ( $query != '' ) {
				if ( $query == 'logout' ) {
					session_unset();
					$response = array( 'Successfully Logged Out' );
				} elseif ( $query == 'clear' ) {
					clear_history();
				} else {
					$mysqli_connection = new mysqli(
						$_POST['mysql-host'],
						$_POST['mysql-user'],
						$_POST['mysql-password'],
						$_POST['mysql-database']
					);
					if ( $mysqli_connection->connect_error ) {
						$response = array( 'Connection Error: ' . $mysqli_connection->connect_error );
					} else {
						$mysqli_query_results = $mysqli_connection->query( $_POST['query'] );
						if ( ! $mysqli_query_results ) {
							$response = array( $mysqli_connection->error );
						} else {
							$response = array();
							while ( $mysqli_connection_result_row = $mysqli_query_results->fetch_array( MYSQLI_ASSOC ) ) {
								$response[] = $mysqli_connection_result_row;
							}
						}
					}
				}
			} else {
				$response = array();
			}
			if ( $query != 'logout' AND $query != 'clear' ) {
				array_push( $_SESSION['mysql_shell_queries'], $query );
				array_push( $_SESSION['mysql_shell_query_responses'], $response );
			}
		}
	}
	
	function clear_history()
	{
		if ( isset( $_SESSION['mysql_shell_logged_in'] ) ) {
			$logged_in = TRUE;
		} else {
			$logged_in = FALSE;
		}
		session_unset();
		if ( $logged_in ) {
			$_SESSION['mysql_shell_logged_in'] = TRUE;
		}
	}
	
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>MySQL Shell Emulator</title>
	<style type="text/css">
		* {
			margin: 0;
			padding: 0;
		}
		body {
			background-color: #000000;
			color: #00FF00;
			font-family: monospace;
			font-weight: bold;
			font-size: 12px;
			text-align: center;
		}
		input, textarea {
			color: inherit;
			font-family: inherit;
			font-size: inherit;
			font-weight: inherit;
			background-color: inherit;
			border: inherit;
		}
		.content {
			width: 95%;
			min-width: 400px;
			margin: 35px auto 20px;
			text-align: left;
			overflow: auto;
		}
		.mysql-shell .settings {
			position: fixed;
			background-color: #000000;
			width: 80%;
			top: 0;
			padding: 10px 0;
		}
		.mysql-shell .settings input {
			border: 1px solid #00FF00;
		}
		.mysql-shell .previous-queries {
			padding: 5px 0;
		}
		.mysql-shell .previous-queries table {
			width: 100%;
			margin: 5px 0 0;
		}
		.mysql-shell .previous-queries table th,
		.mysql-shell .previous-queries table td {
			padding: 4px 6px;
		}
		.mysql-shell #query {
			width: 90%;
		}
		.mysql-shell .colorize {
			color: #0000FF;
		}
	</style>
</head>
<body>
	<div class="content">
		<div class="mysql-shell" id="mysql-shell">
			<?php if ( ! empty( $_SESSION['mysql_shell_queries'] ) ) {  ?>
				<?php foreach ( $_SESSION['mysql_shell_queries'] as $index => $mysql_query ) { ?>
				<div class="previous-queries">
					<pre><?php echo '$ ', $mysql_query, "\n"; ?></pre>
					<pre><?php echo $_SESSION['mysql_shell_query_responses'][$index]; ?></pre>
					<?php if ( is_array( $_SESSION['mysql_shell_query_responses'][$index] )
							AND is_array( $_SESSION['mysql_shell_query_responses'][$index][0] ) ) { ?>
					<table rules="all">
						<thead>
							<tr>
								<?php
									$mysql_query_response_columns = array_keys( $_SESSION['mysql_shell_query_responses'][$index][0] );
									foreach ( $mysql_query_response_columns as $mysql_query_response_column ) {
								?>
								<th><?php echo $mysql_query_response_column; ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $_SESSION['mysql_shell_query_responses'][$index] as $mysql_query_response_index => $mysql_query_response_row ) { ?>
							<tr>
								<?php foreach ( $mysql_query_response_row as $mysql_query_response_column_key => $mysql_query_response_column_value ) { ?>
								<td><?php echo $mysql_query_response_column_value; ?></td>
								<?php } ?>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<?php } else { ?>
					<p>
						<?php echo $_SESSION['mysql_shell_query_responses'][$index][0]; ?>
					</p>
					<?php } ?>
				</div>
				<?php } ?>
			<?php } ?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="queries" id="queries">
				<div class="settings">
					<label for="mysql-host">Host:</label>
					<input type="text" id="mysql-host" name="mysql-host"<?php if ( ! empty( $_POST['mysql-host'] ) ) { ?> value="<?php echo htmlentities( $_POST['mysql-host'] ); ?>"<?php } else { ?> value="localhost"<?php } ?> />
					- <label for="mysql-database">Database:</label>
					<input type="text" id="mysql-database" name="mysql-database"<?php if ( ! empty( $_POST['mysql-database'] ) ) { ?> value="<?php echo htmlentities( $_POST['mysql-database'] ); ?>"<?php } ?> />
					- <label for="mysql-user">User:</label>
					<input type="text" id="mysql-user" name="mysql-user"<?php if ( ! empty( $_POST['mysql-user'] ) ) { ?> value="<?php echo htmlentities( $_POST['mysql-user'] ); ?>"<?php } ?> />
					- <label for="mysql-password">Password:</label>
					<input type="text" id="mysql-password" name="mysql-password"<?php if ( ! empty( $_POST['mysql-password'] ) ) { ?> value="<?php echo htmlentities( $_POST['mysql-password'] ); ?>"<?php } ?> />
				</div>
				$ <?php if ( ! isset( $_SESSION['mysql_shell_logged_in'] ) ) { ?>Password:
				<input type="password" name="query" id="query" />
				<?php } else { ?>
				<input type="text" name="query" id="query" autocomplete="off" onkeydown="return query_keyed_down(event);" />
				<?php } ?>
				<input type="submit" style="visibility: hidden;" />
			</form>
		</div>
	</div>
	<script type="text/javascript">
		
		<?php
			$single_quote_cancelled_queries = array();
			if ( ! empty( $_SESSION['mysql_shell_queries'] ) ) {
				foreach ( $_SESSION['mysql_shell_queries'] as $query ) {
					$cancelled_query = str_replace( '\\', '\\\\', $query );
					$cancelled_query = str_replace( '\'', '\\\'', $query );
					$single_quote_cancelled_queries[] = $cancelled_query;
				}
			}
		?>
		
		var previous_queries = ['', '<?php echo implode( '\', \'', $single_quote_cancelled_queries ) ?>', ''];
		
		var current_query_index = previous_queries.length - 1;
		
		document.getElementById( 'query' ).focus();
		
		window.scrollTo( 0, document.body.scrollHeight );
		
		function query_keyed_down( event )
		{
			var key_code = get_key_code( event );
			if ( key_code == 38 ) { //Up arrow
				fill_in_previous_query();
			} else if ( key_code == 40 ) { //Down arrow
				fill_in_next_query();
			}
			return true;
		}
		
		function fill_in_previous_query()
		{
			current_query_index--;
			if ( current_query_index < 0 ) {
				current_query_index = 0;
				return;
			}
			document.getElementById( 'query' ).value = previous_queries[current_query_index];
		}
		
		function fill_in_next_query()
		{
			current_query_index++;
			if ( current_query_index >= previous_queries.length ) {
				current_query_index = previous_queries.length - 1;
				return;
			}
			document.getElementById( 'query' ).value = previous_queries[current_query_index];
		}
		
		function get_key_code( event )
		{
			var event_key_code = event.keyCode;
			return event_key_code;
		}
		
	</script>
</body>
</html>