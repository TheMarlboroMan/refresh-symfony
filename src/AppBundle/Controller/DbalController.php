<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DbalController extends Controller {

	public function dbal1Action() {

		$conn=$this->get('database_connection');

		$dump_data=function($title, $contents, $extra=null) {
			return <<<R
<p>Using {$extra}{$title}</p><pre>{$contents}</pre>
R;
		};

		$methods=['fetchAll', 'fetchArray', 'fetchAssoc'];
		$query_select="SELECT * FROM contacts LIMIT 3 OFFSET 0;";

		try {
			foreach($methods as $key => $m) {
				echo $dump_data($m, print_r($conn->$m($query_select), true));
			}
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			echo "<p>There was an error using the fetchFunctions!</p>";
		}
		
		//Using query... $statement will be a Doctrine\DBAL\Driver\PDOStatement object. Not all its methods are tested here.
		try {
			$statement=$conn->query($query_select);
			echo $dump_data('Doctrine\DBAL\Driver\PDOStatement::fetchAll', print_r($statement->fetchAll(), true));
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			echo "<p>There was an error using the Doctrine\DBAL\Driver\PDOStatement object!</p>";
		}

		die();
	}

	public function dbal2Action() {

		$conn=$this->get('database_connection')->getWrappedConnection();

		die(get_class($conn));
	}
}
