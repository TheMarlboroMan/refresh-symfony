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

	public function dbal2Action($name, $phone) {

		$conn=$this->get('database_connection')->getWrappedConnection();

		$view_params=['results' => null, 'name' => $name, 'phone' => $phone];

		try {
			$query_select="SELECT * FROM contacts WHERE name LIKE :name OR phone LIKE :phone;";
			$prep_statement=$conn->prepare($query_select);
			$prep_statement->execute([':name' => "%$name%", ':phone' => "%$phone%"]);
			$view_params['results']=$prep_statement->fetchAll();
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			//Do nothing... the twig template will display an error message when $view_params['results'] is null.
		}

		return $this->render('contacts-result.html.twig', $view_params);
	}

	public function dbalTransactionsAction() {

		$conn=$this->get('database_connection')->getWrappedConnection();

		//This...

//		$conn->beginTransaction();
//		$conn->exec("CALL create_contact('Titus', '123456789', 'titus@titus.com');");
//		$conn->exec("CALL create_contact('Jack ', '987654321', 'jack@jack.com');");
//		$conn->commit();

		//Will never work.
		//I am sorry, but you won't be able to "exec" more than one "call"... This is not 
		//something wrong with symfony or doctrine, rather something odd with PDO: exec will
		//return an integer with the affected rows. A second call will issue a 
		//"SQLSTATE[HY000]: General error: 2014" error complaining about unbuffered queries...

		//However, you are welcome to avoid using exec and do the job of preparing statements and closing the cursor:

		$conn->beginTransaction();

			$stmt_1=$conn->prepare("CALL create_contact('Titus1', '123456789', 'titus@titus.com');");
			$stmt_1->execute();
			$stmt_1->closeCursor();

			$stmt_2=$conn->prepare("CALL create_contact('Titus2', '123456789', 'titus@titus.com');");
			$stmt_2->execute();
			$stmt_2->closeCursor();

			$stmt_3=$conn->prepare("CALL create_contact('Titus2', '123456789', 'titus@titus.com');");
			$stmt_3->execute();
			$stmt_3->closeCursor();

		$conn->commit();

		$conn->beginTransaction();

			$stmt_4=$conn->prepare("CALL create_contact('You will never see me.', '000000000', 'invisible@invisible.com');");
			$stmt_4->execute();
			$stmt_4->closeCursor();

		$conn->rollBack();

		//You may even ease your pain by creating a few wrappers around that. Here is a quick anonymous
		//function, but you can (and must) do much better, like a full class wrapper around your procedures
		//that really takes advantage of "prepare".

		$call_and_discard=function(\Doctrine\DBAL\Driver\PDOConnection &$conn, $qstr) {
			$stmt=$conn->prepare($qstr);
			$stmt->execute();
			$stmt->closeCursor();
		};

		$conn->beginTransaction();
		$call_and_discard($conn, "CALL create_contact('Jack1', '123456789', 'jack@jack.com');");
		$call_and_discard($conn, "CALL create_contact('Jack2', '123456789', 'jack@jack.com');");
		$call_and_discard($conn, "CALL create_contact('Jack3', '123456789', 'jack@jack.com');");
		$conn->commit();

		//Just so you know, I tried to do manually set the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY as the error message suggests.
		//It didn't work.

		//$conn->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		//$conn->beginTransaction();
		//	$conn->exec("CALL create_contact('Titus1', '123456789', 'titus@titus.com');");
		//	$conn->exec("CALL create_contact('Titus2', '123456789', 'titus@titus.com');");
		//	$conn->exec("CALL create_contact('Titus2', '123456789', 'titus@titus.com');");
		//$conn->commit();

		return $this->render('first-template.html.twig', ['something' => "Now try and look for 'never' in dbal/2"]);
	}
}
