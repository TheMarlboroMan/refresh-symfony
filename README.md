# Refresh Symfony

A small project created to remember how Symfony 2.8 works and also serve as a reference... This is not meant to be a symfony crash course, but rather a refresher (hence the name).

It will cover the next steps:

- Creating the project and setting it up.
- Setting it up on GIT.
- Creating a quick controller.
- Using twig.
- Databases and doctrine.
- Custom services.
- Forms.
- Security and users.

Each step will have examples of code in the repository. All work will be done with new examples so the code in the repository is always "final", that is, new features are introduced in new modules of the final application. It is recommended that the steps are followed one by one since the work is incremental. The idea is that all sections can be read in order and their instructions carried out so in the end we have some basic Symfony knowledge.

One more thing, topics are not restricted to their chapter (for example, we see some more twig features in the database chapter). Knowledge is just added incrementally.

## Creating the project and setting it up.

First things first, this is how you create the project.

	symfony new project_name version

For my version of PHP I need to go symfony 2.8 so...
	
	symfony new refresh_symfony 2.8

Next it would be a good idea to check the configuration by navigating to:

	localhost/refresh_symfony/web/config.php

It is likely that it will complain about the cache and log directories not being readable. Fix that, either by 0777 them or, if you are a subtle person, chowning them to the daemon user in the daemon group (mind you that this may give you problems down the line when you run console commands).

	sudo chown daemon:daemon app/cache
	sudo chown daemon:daemon app/logs

In any case, fix whatever you need from "config".

## Setting it up on GIT...

This one is easy:

	git init
	git add whatever files you need
	git commit -m 'Whatever commit message you want'
	git remote add origin url_here
	git push -u origin master

The -u is to set the tracking of your branches.

## Creating a quick controller.

This should be done in two parts: the route and the controller. Let us start with the route. Open the file at:

	app/config/routing.ytml

You can add your route to it like this:

routename:
    path: path-to-your-route
    defaults: {_controller: BundleName:ControllerClassName:actionmethodname}

For example:

my-first-route:
   path: this-is-my-first-route
   defaults: {_controller:AppBundle:FirstRoute:showFirstRoute}

Now, remember that YAML doesn't want tabs there. Anyway, you can try and navigate to 

	http://localhost/refresh_symfony/web/app_dev.php/this-is-my-first-routename

And will be able to watch symfony complain of the non existing controllers...  In particular it will say that there is no AppBundle\Controller\FirstRouteController so...

	touch src/AppBundle/Controller/FirstRouteController.php

Notice how the filename matches both the class name we will use (FirstRoute) and the Controller word. Try and load again. This time it will complain that the controller class is not inside the file. Create it, you can use something like this:

	<?php
	namespace AppBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Response;

	class FirstRouteController extends Controller {

		public function showFirstRouteAction() {

			return new Response("<html><body>Hello!</body></html>");
		}
	}

And you are done!. Let us take a closer look to what we've done in the controller. First, we declare our namespace: AppBundle\Controller. This matches the directory where the file can be found. Next we import the Controller and Response classes. The Controller class is used to extend our own class (notice the name, FirstRouteController) and the Response will be used to return a Response object. With this simple configuration we don't really need to extend Controller, but it has many helper functions to render templates, work with services and such... Finally we declare a public method (showFirstRouteAction, whose name is composed of the value we gave in the routing file and the word action). All it does is return a response object.

Take a look at how the two halves match:

	_controller:AppBundle:FirstRoute:showFirstRoute

	namespace AppBundle\Controller;
		class FirstRouteController
			public function showFirstRouteAction

And that's about it.

## Using twig.

Next we are off to use a bit of twig in many levels. We will create a base view template and from there extend with other functionality. 

### The master template.

First, create the template file.

	touch app/Resources/views/master.html.twig

Take a look at the file in the repository. We did different things in the different blocks.

For the stylesheets:

	- We added bootstrap from its CDN and our own css/main.css asset.
		- The bootstrap thing is added "raw", from the outside.
		- The main.css file is added with "asset", which controls our assets within web/
			- The css file does not exist so mkdir web/css and touch web/css/main.css. 
			- The contents of the file can simply be "body {background-color: #000; color: #fff;}".
	
For the title:

	- We added a default title.

For the body:

	- We took advantage of bootstrap to present a simple layout with a header, two columns and a footer with a simple twig filter.

We are going to hook our previous controller to this view. Edit the FirstRouteController file so now it goes like this:

	<?php
	namespace AppBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	class FirstRouteController extends Controller {

		public function showFirstRouteAction() {

			return $this->render('master.html.twig', []);
		}
	}

And try to navigate to the route. You should see that both the bootstrap and our own css are loaded and that we have a template in place. 

### The secondary template.

We will create a secondary template for our route in which we will alter a few blocks to demonstrate how it goes. In this case, we will alter the controller again. Instead of rendering master.html.twig we will ask it to render "first-template.html.twig". We'll also leave the second parameter empty for now.

	return $this->render('first-template.html.twig', []);

Of course, there is no way this is going to work, since the template file does not exist. Fix that:

	touch app/Resources/views/first-template.html.twig

Take a look at the file in the repository and see what we did here:

	- The first thing that must bee seen in an extended template is the "extends" command.
	- We used the parent() contents from the stylesheets block, but added a new asset.
		- The asset does not exist so touch web/css/first-route.css and add ".explanation {font-size: 1.5em; color: red;}" to its contents.
	- We changed the title block. The HTML title should be different that in the master.
	- We changed the body block, so there's no more two-column layout. We also added some logic in twig that we'll use shortly.

You can now navigate to the route and check that everything has changed.

The final part would be to pass a variable to twig. The logic asked for a "something", and we will give something to it by changing the controller:

	return $this->render('first-template.html.twig', ['something' => 'A new beginning']);

That concludes the twig crash course.

## Databases and Doctrine.

Doctrine is huge and I can't quite see the point in many of its features (do I really need a query builder? do I really want to delegate database related code to my PHP code?... but then again, that's strictly personal). Still, that's the ORM bundled with Symfony and even if we could use another (remember that Symfony uses some parts of Doctrine even for non-database stuff, so no complete removal for you), I think of it as kind of a... default.

### Configuration

The first thing we need to do is to have a database running. In this case, with Xampp installed I am running a MariaDB database that luckily behaves like MySQL. The access parameters must be set in the app/config/parameters.yml file:

    database_host: Your host... most likely "localhost" if you are developing locally.
    database_port: The port... You can leave it at null for sensible defaults.
    database_name: The name of the database we are going to use.
    database_user: A user name.
    database_password: The pass of the user.

These values will later be imported into app/config/config.yml. If we want to know if it works we can add something simple to the controller:

	$conn=$this->get('database_connection');
	list ($something)=$conn->fetchArray("SELECT NOW() AS now FROM DUAL");
	return $this->render('first-template.html.twig', ['something' => $something]);

If we see an error, then we did something wrong (maybe the database does not exist).

### Troubleshooting with XAMPP.

If you are working with XAMPP and cannot execute the symfony console commands that will follow in this section it is likely that you may face one of these problems:

- Can't open mysql in default socket:
	- This may be caused because you have two php clients... 
		- Try using the lampp one as in /opt/lampp/bin/php app/console doctrine:schema:update.
		- You can add the path to your PATH variable so this becomes the default.
	- Or maybe the mysql socket of lampp is not in /var/run/mysqld/mysqld.sock, as you could expect. It is actually at /opt/lampp/var/mysql/mysql.sock. Find out which php.ini file are you using and add that socket to the pdo_mysql.default_socket key. Incidentally this is related to the problem where you cannot run the mysql client from the command line... Try using the --socket socket/path argument to the command line.

- Cannot write to log files:
	- This one is ugly. The user of your php cli and the user of your Xampp PHP are different. You can do a few things here, some may work, some may not.
		- sudo the command (sudo app/console generate:doctrine:entities AppBundle) and forget about it.
		- Add your user to the daemon group and set appropriate permissions for the files and directories.
			- sudo usermod -a -G daemon your_user
			- log out and log in so the user group thing refreshes.
			- sudo chmod -R 0775 app/logs
			- sudo chmod -R 0775 app/cache
		- If you are inclined to change the user Apache runs as or to use ADL follow the solutions in https://symfony.com/doc/2.8/setup/file_permissions.html

### Entities and mapping.

For this example we will create a very simple structure of entities: we want a list of things people borrowed from us. First we will describe the mapping.

	mkdir -p src/AppBundle/Resources/config/doctrine/
	touch src/AppBundle/Resources/config/doctrine/BorrowedItem.orm.yml

That is the mapping file, it will be used to represent the borrowed item with its name, name of the person who borrowed and time when they borrowed it. Take a look at the contents of the file in the project. Notice how we use camelcase for property names: it will come handy later when using repositories.

Now, you could use the symfony console to generate the Entity file. If you're having problems with this, try checking the troubleshooting section above:

	php app/console generate:doctrine:entities AppBundle

This will auto generate the entity class in AppBundle/Entity. All properties will preserve their names but method getters and setters will use camelcase (as in date_borrowed becomes getDateBorrowed and setDateBorrowed). If you want, you can simply create the file yourself and populate it with a class in the AppBundle\Entity namespace, with its getters and setters. In any case, I don't think it's a good idea to add any logic in that class, so you may run the console command and forget you ever saw that.

Next step, let us check if the schema is up to date with the database...

	php app/console doctrine:schema:validate

There will be an error because there is no borroweditems table. It is actually a good idea to validate the schema as you go on adding entities... Anyway, we need to create the table. At this point we can either proceed Doctrine style or classic style. To do it Doctrine style:

	php app/console doctrine:schema:create

That will create the borroweditems table. If you wish to proceed classic style just log into your database and create the tables as you have always done.

### Persisting data.

In order to persist our data we are going to create a new controller. Choose your own route and controller name and do the steps seen in the section "Creating a quick controller". Use this code for the controller:

	<?php
	namespace AppBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use AppBundle\Entity\BorrowedItem;

	class ChooseYourOwnNameController extends Controller {

		public function ChooseYourOwnNameAction() {

			$em=$this->get('doctrine')->getManager();

			$setvalues=function(array $data) use ($em) {
				$item=new BorrowedItem;
				$item->setName($data[0]);
				$item->setBorrowedFrom($data[1]);
				$item->setDateBorrowed($data[2]);
				$em->persist($item);
			};

			$data=[ ["Snow Crash book", "My brother", new \DateTime('2015-01-12')],
				["A wireless keyboard", "My Mother", new \DateTime('2015-02-22')],
				["My Dark Side of the Moon record", "My Father", new \DateTime('2017-04-25')]];

			array_walk($data, $setvalues);

			$em->flush();

			return $this->render('master.html.twig');
		}
	}

Navigate to it. You should see the master response and there should be three new entities in the table.

### Retrieving data.

We are going to be adding a new action to the controller used in the previous section. Do the work you need to set up your routes and use this code for the action:

	public function yourActionNameHereAction() {

		$retrieved_item_names=[];
		$append_data=function(BorrowedItem $item=null) use (&$retrieved_item_names) {
			if($item) {
				$retrieved_item_names[]=$item->getName().' from '.$item->getBorrowedFrom();
			}
		};

		$repository=$this->get('doctrine')->getRepository(BorrowedItem::class);
		$append_data($repository->find(1));
		$append_data($repository->findOneByName('My Dark Side of the Moon record'));
		$append_data($repository->findOneByBorrowedFrom('My Mother'));
		$append_data($repository->findOneById(9999));

		return $this->render('first-template.html.twig', ['something' => implode($retrieved_item_names, ', ')]);
	}

This should display the first-template with a list of items and the people items are borrowed from. Here is where using the camelcase convention for entity properties pays off: you can use automated repository functions that you did never declare, such as findOneByName or findOneByBorrowedFrom.

### Updating data.

In order to update your tables, setup a new action and use this code that combines techniques seen in the two previous sections:

	public function yourUpdateNameHere() {

		$item=$this->get('doctrine')->getRepository(BorrowedItem::class)->find(1);
		$item->setName("Antique jacket")->setDateBorrowed(new \DateTime())->setBorrowedFrom("My friend");

		$em=$this->get('doctrine')->getManager();
		$em->persist($item);
		$em->flush();

		return $this->render('master.html.twig');
	}

Check with your database that data was changed.

### Removing data.

Same as before. Setup a new action with this:

	public function yourDeleteNameHereAction() {

		$d=$this->get('doctrine');
		$item=$d->getRepository(BorrowedItem::class)->findOneByBorrowedFrom('My Mother');

		$em=$d->getManager();
		$em->remove($item);
		$em->flush();

		return $this->render('master.html.twig');
	}

And check with your database.

### Custom repositories

We are going to add a custom repository. For that, we will first create a new Entity so we don't have to touch our previous examples. This entity will be a "person", with name and surname. Follow these steps:

	- Create the mapping file with the id, name and surname fields. 
	- Run "php app/console doctrine:schema:validate". It will tell you that there's no Person entity.
	- Run "php app/console doctrine:generate:entities AppBundle:Person --path src. "people" is the name of the table in the mapping file. 
	- Run "php app/console doctrine:schema:validate". It will tell you that there's no sync with the database (there's no people table).
	- Run "php app/console doctrine:schema:update". We used "create" before because there were no tables... Now we should use update. It will tell us how many changes are needed, but will not execute them.
	- You can now choose one of these options:
		- "Run "php app/console doctrine:schema:update --dump-sql" to have the console tell you the SQL you need to execute on your server.
		- "Run "php app/console doctrine:schema:update --force" to have Doctrine run the SQL for you.
	- Add this line to your repository class at the same level as the "type" key: "repositoryClass: AppBundle\Repository\PersonRepository"
		- This will let you map your entity to your repository class!. If you fail to do this or mistype the name you'll get errors.
	- Create the repository file and directory:
		- mkdir src/AppBundle/Repository
		- touch src/AppBundle/Repository/PersonRepository.php
	- Now for the fun part. The class must:
		- Exist in the correct namespace (in this case AppBundle\Repository)
		- Extend from Doctrine\ORM\EntityRepository
		- Be named correctly (in this case PersonRepository, as in classname + repository).

Our example code includes a bit of DQL. If you think DQL is one too many layers of abstraction, I agree with you.

	<?php
	namespace AppBundle\Repository;

	use Doctrine\ORM\EntityRepository;

	class PersonRepository extends EntityRepository {

		public function findAllNameLike($name) {

			$dql_string="SELECT p FROM AppBundle:Person p WHERE p.name LIKE :paramname ORDER by p.name ASC";

			return $this->getEntityManager()
				->createQuery($dql_string)
				->setParameter('paramname', '%'.$name.'%')
				->getResult();
		}
	}

Create a route and a controller to test this. In our example we created a route that needs a parameter (a word) that will be passed to the repository method:

	repository-person-test:
	   path: tests/repository-person/{paramname}
	   defaults: {_controller:AppBundle:Tests:usePersonRepository}
	   requirements:
	     paramname: '[a-zA-z]+'

This parameter enters as the method's parameter as long as they are named the same. The parameter is passed to the repository:

	public function usePersonRepositoryAction($paramname) {

		$people=$this->get('doctrine')->getRepository('AppBundle:Person')->findAllNameLike($paramname);

		$contents="No people found by ".$paramname;
		if(count($people)) {
			$contents=substr(array_reduce($people, function($carry, Person $item) {$carry.=$item->getName().' '.$item->getSurname().', '; return $carry;}, "People found by ".$paramname.": "), 0, -2);
		}

		return $this->render('first-template.html.twig', ['something' => $contents]);
	}

One thing of note: using custom repositories does not disable the "built in" findBy methods, so this would still work even if we didn't declare it:

	$this->get('doctrine')->getRepository('AppBundle:Person')->findByName('Peter');

### Relationships

Entities can be related to one another taking advantage of foreign keys in your database. To demonstrate that we are going to create two new enties and relate them. Our two entities will be a contact book and a contact. A contact book has a name and many contacts and a contact has a name, a phone number, an email and belongs to a single contact book. 

Start by creating the mapping files and entities of the Contact:

	- Touch touch src/AppBundle/Resources/config/doctrine/Contact.orm.yml
	- Create the mapping as you would usually do, adding the id, name, phone and email.
	- Because a many contacts are in a single contact book, this is a "manyToOne" relationship. This must be mapped in the YAML as follows, having the "manyToOne" key in the same level as "fields" or "type".

	manyToOne:
	  book:
	    targetEntity: ContactBook
	    inversedBy: contacts
	    joinColumn:
	      name: contact_book_id
	      referencedColumnName: id

	- It is easily explained: we want a property named "book", which will be of the type ContactBook (in ContactBook there will be an inverse "contacts", with a list of Contact) and joined by the local column "contact_book_id" to the id column of the ContactBook entity table.
	- As usual, run "php app/console doctrine:schema:validate" and it will tell you that there is no contact entity.
	- Try to run "php app/console doctrine:generate:entities AppBundle:Contact --path src"... It will create the entity even when the ContactBook entity does not exist.

Let us continue by creating the mapping files and entities of the ContactBook:

	- touch src/AppBundle/Resources/config/doctrine/ContactBook.orm.yml
	- Create the mapping as you would usually do, adding the id and the name. Add also the repositoryClass entry as we will use it later.
	- Because a contact book is related to many contacts and we had a "manyToOne" on the other side, use "oneToMany" here:

	   oneToMany:
	      contacts:
		targetEntity: Contact
		mappedBy: book
	
	- Again, that's easily explained. "contacts" is the "inversedBy" declared before. It will be a list of entities of the type Contact. "book" is the name of the property in the Contact entity that represents the Contact book.
	- Validate the schema and note the errors.
	- Run "php app/console doctrine:generate:entities AppBundle:ContactBook --path src"

Now validate the schema again. The tables do not exist, so do the schema update as in the previous sections. Take a moment to insert data into the new tables but bear in mind that you must create contact books first and contacts second, as there's a foreign key dependant on contacts. Also, if you feel adventurous, try to delete a contact book once contacts are created and rejoice in the glory of foreign key constraints. There are "cascade" entries you can use in Doctrine to do this, if you feel inclined to do so.

Picking up where we left off, create the repository file for the ContactBook entity as you did before. This time we will not use DQL but the query builder (if you thought DQL was redundant, this will give you a stroke):

	- touch src/AppBundle/Repository/ContactBookRepository.php
	- Create the class following the instructions outlined in the "custom repositories" section. 
	- Create the method that returns books with more than N contacts within. The code follows:

	public function findByContactsGreaterThan($number) {

		$qb=$this->getEntityManager()->createQueryBuilder();
		return $qb->select('cb')
			->from('AppBundle:ContactBook', 'cb')
			->join('cb.contacts', 'c')
			->groupBy('cb.id')
			->having($qb->expr()->gt($qb->expr()->count('c.id'), $number))
			->getQuery()
			->getResult();
	}

If you thought DQL was a layer of abstraction too many... What about that?. How is that any more readable than "SELECT cb.* FROM contact_books cb LEFT JOIN contacts c ON cb.id=c.contact_book_id GROUP BY cb.id HAVING COUNT(cb.id) > 1"? Fortunately, nobody is forcing anybody to do that but well, if having your SQL statements in PHP can be considered breaking encapsulation I don't know what the code above can be considered to be. End of rant.

We are going to check our results, for that we will create a new route and a new controller. This time we'll add the parameter "quantity" in the route with a default value of 0, signifying the $number parameter in findByContactsGreaterThan:

	yournamehere:
	   path: yourpathhere/{quantity}
	   defaults: {_controller: AppBundle:Tests:yourControllerHere, quantity:0}
	   requirements:
	     quantity: '[0-9]+'

Now, before pairing that to a controller we will create a new twig template. We will use it to further enhance the experience of showing data. The file in the repository is called "contacts.html.twig" and has a companion "contact-book.html.twig". Take a look at them in order and reflect upon the things we are doing here:

	- We have a contacts.html.twig template that looks a lot like the other ones, extending fom master and whatnot.
	- We do not declare the stylesheets block, so the one in master is used.
	- There's something new, a for loop in which we iterate all books.
	- Inside the loop there is an "include" function to include the "contacts" template and have the variable "book" of that template correspond to the current book (the with_context=false part is optional, it means that other variables won't be accesible in the included template... It's a matter of personal taste).
	- Inside the contact-book template we are using b (as in book) and (c as in contact) to refer to book and contact objects, calling the methods as we need (we could actually go b.name and twig would infer b.getName() for us).
	- Most importantly, we can use b.getContacts() and get an array of contacts, as it pertains to the mapping we did!.

### Database Layer interaction.

If you wish to keep everything where it belongs, perhaps you'd like to skip the DQL and query building and directly code stored procedures into your database. For this example we will reconstruct the previous controllers using stored procedures and trying to avoid Doctrine queries as much as possible.

First, create a procedure in your database. The following code creates the procedure contact_book_by_quantity, which is meant to replace the code in the contact_book repository:

	DROP PROCEDURE IF EXISTS contact_book_by_quantity;
	DELIMITER //
	CREATE PROCEDURE contact_book_by_quantity(IN _q INTEGER)
	BEGIN
		SELECT cb.* FROM contact_books cb LEFT JOIN contacts c ON cb.id=c.contact_book_id GROUP BY cb.id HAVING COUNT(cb.id) > _q;
	END//
	DELIMITER ;

If you have been following so far, we have seen no way of issuing a SQL command through Doctrine (as we would with mysqli::query or PDO::query... or even mysql_query). However, any EntityManager can issue a call to createNativeQuery to do that. There is, however, a catch: because you are using Doctrine you need to map the results of your query to something, a ResultSetMapping object that must be provided as a second parameter to createNativeQuery. 

To first approach it, create a new controller and action (don't forget to map it in routing.yml!), perhaps a clone of the ones used in the "Relationships" section. Once inside, use this code for the body of the action:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addEntityResult('AppBundle:ContactBook', 'b')
		->addFieldResult('b', 'id', 'id')
		->addFieldResult('b', 'name', 'name');

	$qs="CALL contact_book_by_quantity(?)";
	$books=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->setParameter(1, $quantity)
		->getResult();

	return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);

A dissection:

	- First we create an empty ResultSetMapping object. 
		- We tell it that our query will contain an entity (AppBundle:ContactBook, or AppBundle\Entity\ContactBook for long, with the alias "b").
		- We tell it that there will be two result fields for the entity aliased as "b": the column "id" will be mapped to the field "id" and so on.
		- The interface is fluent: every call returns a reference to the $rsm object, so we can keep chaining them.
	- Next we create the query string. This query string will not be touched by Doctrine, so make sure it is foolproof and try to use parameters to avoid SQL injections.
	- We used "getManager" instead of "getEntityManager", which is due for deprecation.
	- Finally, we ask the entity manager to create the native query, assign the parameter and get its result.

Go ahead and navigate to the controller. The result cannot be distinguished from our previous work, including the number of queries done: one for your procedure and one more for each collection of contacts (you can see that in the debug bar). In the background, Doctrine still queries the database for information about the contact entities. Since we are trying to drop all responsibility over the database layer, we can do a bit better. First, create a new procedure with this code (a little something, if you are planning on copying and pasting these to your sql console remove all tabs!!!):

	DROP PROCEDURE IF EXISTS contact_book_by_quantity_full;
	DELIMITER //
	CREATE PROCEDURE contact_book_by_quantity_full(IN _q INTEGER)
	BEGIN
		SELECT cb.id AS cb_id, cb.name AS cb_name, c.id AS c_id, c.name AS c_name, c.phone AS c_phone, c.email AS c_email 
		FROM contact_books cb JOIN contacts c ON cb.id=c.contact_book_id 
		WHERE cb.id IN (SELECT cb.id FROM contact_books cb LEFT JOIN contacts c ON cb.id=c.contact_book_id GROUP BY cb.id HAVING COUNT(c.id) > _q);
	END
	//
	DELIMITER ;

That handful will return as many rows as contacts and every row contains the information for both the contact and also the contact book (yes, there's some repetition going on there). Pay very close attention on how we alias the fields so there are no repeated columns. Also, notice how the foreign key is not selected either.

Next create a new action and route it. It looks almost like the one before:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addEntityResult('AppBundle:ContactBook', 'cb');
		->addFieldResult('cb', 'cb_id', 'id')
		->addFieldResult('cb', 'cb_name', 'name')
		->addJoinedEntityResult('AppBundle:Contact', 'c', 'cb', 'contacts')
		->addFieldResult('c', 'c_id', 'id')
		->addFieldResult('c', 'c_name', 'name')
		->addFieldResult('c', 'c_phone', 'phone')
		->addFieldResult('c', 'c_email', 'email');

	$qs="CALL contact_book_by_quantity_full(?)";
	$books=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->setParameter(1, $quantity)
		->getResult();

	return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);

In detail you can see that:

	- It is almost the same code.
	- We add a joined entity result: the contact, aliased as "c", whose parent is the entity aliased as "cb" (in this case ContactBook, we aliased it as "b" before) and has a property "contacts" to store the "c" entity. 
	- Next we define the fields for "c". I never tried but I think you can do these field definitions in any order you wish.

Try it and check the results: the same, but we did only one database call. That doesn't neccesarily mean faster code, but it means that your database code is in control now. Even better, you no longer need to ask your database specialist to learn DQL, or even better, you can ask your database specialist to write all queries for you. 

Still, this technique allows only for values mapped to entities. What about a simple procedure that counts contacts and groups them by book?. Execute the following in your database prompt:

	DROP PROCEDURE IF EXISTS get_contact_count;
	DELIMITER //
	CREATE PROCEDURE get_contact_count()
	BEGIN
		SELECT cb.id, COUNT(c.id) AS total FROM contact_books cb LEFT JOIN contacts c ON c.contact_book_id=cb.id GROUP BY cb.id;
	END
	//
	DELIMITER ;

This will return a row for each group and the sum of contacts, report-like. There's no entity to map to this report so either we create one (a waste) or we use the scalar mapping technique. Create and route your final action with this code:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addScalarResult('id', 'book_id')
		->addScalarResult('total', 'contacts_total');

	$qs="CALL get_contact_count()";
	$result=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->getResult();

	$reduce=function($carry, array $item) {
		$carry.="Book with id ".$item['book_id'].' has '.$item['contacts_total'].' contacts, ';
		return $carry;
	};

	$contents=substr(array_reduce($result, $reduce, "Book report: "), 0, -2);
	return $this->render('first-template.html.twig', ['something' => $contents]);

An explanation of what it does:

	- Creates a new ResultSetMapping object.
	- This time, we declare that the result has two scalar values whose names match the columns 'id' and 'total' from the procedure and will be aliased as 'book_id' and 'contacts_total'.
	- We execute the query with the ResultSetMapping. The $result variable now contains an array of results. Each result is an array with the keys 'book_id' and 'contacts_total'. If you don't believe me just "dump($results);" and "die();".
	- We create a small reduce function to accomodate the results to the first-template.

With this, you can call any kind of procedure that returns rows and use the data as you see fit while still retaining the ability to use Doctrine (honestly, in this example Doctrine is completely useless anyway). Of course, I forgot to mention that you can put all the ResultSetMapping and native query code into any repository of any entity and have clear, defined and structured code as a result. I leave that as an exercise to you (actually, it boils down to cut and paste the code and return something from the repository method).

### Single results and calling functions.

You may find yourself in a situation when you need to retrieve a value from a database function instead of from a procedure. Albeit rare, it is something that can happen and you won't be able to solve with a bit of extra work. Assume the following function:

	DROP FUNCTION IF EXISTS get_total_contact_count;
	DELIMITER //
	CREATE FUNCTION get_total_contact_count()
	RETURNS INTEGER
	BEGIN
		DECLARE total INTEGER UNSIGNED DEFAULT 0;
		SELECT COUNT(id) INTO total FROM contacts;
		RETURN total;
	END
	//	
	DELIMITER ;

Using the previous techniques will not work:

	- To begin with, you cannot issue a "CALL get_total_contact_count()" to your database. Go ahead and try it. CALL is reserved for procedures.
	- Even if you could, there is no mapping to speak of. What's the name of the returned field?. Is there even a returned field?.

The solution is actually using an old trick. Think about how you call functions in your database and what can you do with them:

	- You SELECT your functions.
	- You can SELECT them into an alias or local variable.

To test it, create and route a new action and use this code:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addScalarResult('total_count', 'this_does_not_really_matter');

	$qs="SELECT get_total_contact_count() AS total_count FROM DUAL";
	$result=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->getSingleScalarResult();

	return $this->render('first-template.html.twig', ['something' => 'There are '.$result.' contact(s) in the whole database']);

Notice that:

	- We used getSingleScalarResult() because we know there is a single value being returned. Previously we have been using getResult() which in this case would have resulted in an array that looks like [0 => ['total_count' => #]], which would have forced us to write 'There are '.$result[0]['total_count'].' contact(s) in the whole database'.
	- We still need to add a scalar result to the mapping, even if the chosen alias does not appear anywhere else.

What about functions that may or may not return a value?. Use the following code to create a procedure that may not return anything:

	DROP PROCEDURE IF EXISTS get_contact_info_by_id;
	DELIMITER //
	CREATE PROCEDURE get_contact_info_by_id(_id INTEGER)
	BEGIN
		SELECT name, email, phone FROM contacts WHERE id=_id;
	END
	//
	DELIMITER ;

Try the function in your database console. Depending on your input, it will return a row with the stated values or will return nothing. Because of that, you need code like:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addScalarResult('name', 'contact_name');
	//We are using only the name: no reason to add more results...

	$qs="CALL get_contact_info_by_id(?);";
	$result=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->setParameter(1, $id)
		->getResult();

	$result_name=count($result) ? $result[0]['contact_name'] : 'nobody';
	return $this->render('first-template.html.twig', ['something' => 'With the id '.$id.' you can find '.$result_name]);

In other words, you need to manually check the number of rows in your result and then access the fields you need: that's actually a few points where you could mess up (field names, remembering $result is an array of arrays, remembering to check the length of $result...) to achieve something so simple. The NativeQuery class of Doctrine gives us an slightly easier alternative:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addScalarResult('name', 'contact_name');

	$qs="CALL get_contact_info_by_id(?);";
	$result=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->setParameter(1, $id)
		->getOneOrNullResult();

	$result_name=$result ? $result['contact_name']: 'nobody';
	return $this->render('first-template.html.twig', ['something' => 'With the id '.$id.' you can find '.$result_name]);

We use the getOneOrNullResult() so $result is null if nothing is found or just a single row if everything went ok. We still need to check, but no count and no direct index accessing. Still, it could be a bit better. What about washing your hands and dumping that work in the template developer?.

	- Create a new template file (touch app/Resources/views/search-contact.html.twig). Take a look at the template and notice how we compose everything in twig.
		- We include the id variable too, just for fun.
		- We use "is null" to check what we need to ouput.
		- All presentation logic is in the template now.
	- Create the controller and route. Use this code:

	$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
	$rsm->addScalarResult('name', 'contact_name');
	//No need to map all values if we aren't going to use them.

	$qs="CALL get_contact_info_by_id(?);";
	$result=$this->get('doctrine')->getManager()
		->createNativeQuery($qs, $rsm)
		->setParameter(1, $id)
		->getOneOrNullResult();

	return $this->render('search-contact.html.twig', ['result' => $result, 'id' => $id]);

That code is actually very clear and concerned only with getting the information. All transformations and checks are done in the template where information will be displayed.

### Exceptions in procedures.

A quick note: if you are into following the spirit of keeping your database concerns inside your database perhaps you'd like to know how procedure exceptions are reported when they are thrown within the context of a NativeQuery call. This code will create a function that will return true when its parameter is odd and throw and exception when it is even.

	DROP FUNCTION IF EXISTS odd_even;
	DELIMITER //
	CREATE FUNCTION odd_even(_value INTEGER)
	RETURNS BOOLEAN
	BEGIN
		IF NOT MOD(_value, 2) THEN SIGNAL SQLSTATE '45000';
		END IF;
	
		RETURN TRUE;
	END
	//
	DELIMITER ;

In case you are wondering, this comes straight from the MySQL docs: 'To signal a generic SQLSTATE value, use '45000', which means "unhandled user-defined exception."'. To check how this exception propagates to our Symfony code, create a controller, an action and a route in which a number is passed as a parameter (you can find examples on how to do that in earlier chapters). Use this as the code for your action:

	public function yourActionNameGoesHereAction($value) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('result', 'result');

		$qs="SELECT odd_even(?) AS result FROM DUAL";

		$value_type=null;

		try {
			$result=$this->get('doctrine')->getManager()
				->createNativeQuery($qs, $rsm)
				->setParameter(1, $value)
				->getSingleScalarResult();
			$value_type='odd';
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			$value_type='even';
		}

		return $this->render('first-template.html.twig', ['something' => $value.' is '.$value_type]);
	}

The code itself does nothing useful, but notice how we wrap the database call in a try-catch block. The exception flows into the PHP code and is caught as such (particularly into a \Doctrine\DBAL\Exception\DriverException, but you can get it with \Exception too if you trust your code enough). Think for a minute on how convenient that is. 

Of course, that would work the same in a stored procedure:

	DROP PROCEDURE IF EXISTS create_contact;
	DELIMITER //
	CREATE PROCEDURE create_contact(IN _name VARCHAR(50) , IN _phone VARCHAR(20), IN _email VARCHAR(100))
	BEGIN
		IF _name='Mark' THEN SIGNAL SQLSTATE '45000';
		END IF;

		INSERT INTO contacts(name, phone, email) VALUES (_name, _phone, _email);
		SELECT LAST_INSERT_ID() AS result;
	END
	//
	DELIMITER ;

As an excercise to you, create a route, controller and action for this procedure that receives the information (name, phone, email) in three separate parameters separated by asterisks in the URL. Handle the possible exception in your controller and do all logic in a new template (show the id for the newly created contact as returned in the procedure or show an error message because the contact cannot be called "Mark"). You can check yourself with the route named "procedure-database-exception".

A few things of note:

	- Pay close attention to how we alias the LAST_INSERT_ID() function in the procedure. 
	- Pay close attention to how we map this alias to an array key with the ResultSetMapping object (if we didn't do that there would be no results!).
	- Have you already noticed the problem with that procedure?. That's right: contacts are not assigned to any contact book.

### Getting closer to PDO.

During the last chapter we have been twisting the ORM Doctrine components to get closer to the database using NativeQuery objects. These techniques still bind us to ResultSetMapping, which may be undesirable in some contexts. In this chapter we will get as close to PDO as we can within the limits imposed by Doctrine, which is pretty close.

The first step is to use Doctrine DBAL (Database Abstraction Layer) Connection class (Doctrine\DBAL\Connection, to be exact), which is a service available in all controllers (that properly extend the controller class, that is). This class features methods that will allow you to work in a manner more closely resembling the PDO interface. Study the example of the route dbal-1 but pay not much mind: we can still (and will) get closer to PDO, this Connection class is just a step in the way. Note that:

	- The Connection class is instantiated as a service ($this->get('database_connection')).
	- It resembles PDO, with its exec, query and prepare methods.
	- You should "prepare()" any statement that may be repeated over and over. The return value of prepare() is a Doctrine\DBAL\Statement object, which in time wraps Doctrine\DBAL\Driver\Statement, which is a wrapper for a part of PDOStatement (see a pattern there?). 
	- You need to choose the correct method for the correct SQL command ("exec()" will not do for a SELECT clause, for example).
	- We skipped some of its methods, like update, insert, delete, lastInsertId, exect... You can find the full public interface in the Doctrine docs.
	- Merrily enough, the docs will tell you that query() gets no parameters (it actually uses PHP5.6 variable arguments feature).
	- We also skipped the template related code this time. Notice that this is not symfony-like!. An action in a controller exists to render sensible code for to the clients who request it. What we did here is not sensible code by any means.

However, if you want to get closer to PDO just skip the Connection and try to go a step further. Instead of getting the database_connection service, we will attempt to get a WrappedConnection object from it with:

	$wr=$this->get('database_connection')->getWrappedConnection();

If we got Doctrine\DBAL\Connection before, the wrapped connection implements the interface Doctrine\DBAL\Driver\Connection and, depending on your driver (you can see those in the config.yml file) it will be an instance of this or that class. In our case we get a Doctrine\DBAL\Driver\PDOConnection (after all, we are talking PDO here) that extends the PDO class. In other words, it can behave like PDO but with more indirection levels. In total, it has a couple of methods not present in PDO (namely requiresQueryForServerVersion and getServerVersion) and overrides a few of them:

	- __construct: Sets the error reporting mode to exceptions (something you have to do manually in PDO) and the statement class to the Doctrine one (instead of the default PDOStatement).
	- exec: Calls PDO::exec inside a try-catch block.
	- prepare: Calls PDO::prepare inside a try-catch block.
	- query: Wraps PDO::query with its four flavours (each with a different set of parameters) and adds a try-catch block.
	- quote: directly calls PDO::quote. Even its default parameter is the same as PDO (thus, this method does nothing new and could be disposed of).
	- lastInsertId: same as above. As disposable as it too.

So... you can just use it as if it were your own PDO class, minding that statements returned by calls to prepare() and query() will be of the Doctrine type Doctrine\DBAL\Driver\PDOStatement. If you are wondering about the differences between that class and PDOStatement I won't bore you with them: Doctrine\DBAL\Driver\PDOStatement extends PDOStatement and the methods it overrides wrap everything in try-catch blocks. With that in mind, take a look at the action mapped by the route "dbal-2" and notice how:

	- Doctrine and symfony took care of connecting to the database.
	- You issued the queries through the DBAL interface.
	- Queries are not caught by the symfony debug bar.
	- You found again the pesky LIKE syntax for query parameters: you need to add the % wildcard in the parameter and not in the query.

### Doctrineless symfony.

In the previous example we used Doctrine with only the DBAL (Database Abstraction Layer) components (without the ORM - Object Relational Mapping - parts). If you are interested in completely do away with Doctrine (maybe you are using symfony to create a REST API and your database administrator is a demigod that did procedures for absolutely everything), please, check my doctrineless-symfony repository, which will follow a format close to this one and will use pure PDO to work with database stuff.

### Last words on databases and doctrine.

These are a few things I purposedly left behind and some thoughts you may be interested in:

	- Yes, there is a way to map the IN/OUT/INOUT parameters of your database procedures to PHP variables. No, I am not going to put any of that here. Honestly, when was the last time you were justified on doing that?
	- Remember that you can (and should) put your ResultSetMapping objects within their respective entity repositories. If you are abusing a particular method of a repository you may wish to "cache" that ResultSetMapping object through a service (we'll see them in the next chapter) that returns it or through static private properties of the repository (I actually prefer the former, as the latter seems a bit un-symfony-ish.
	- We didn't touch a bit about transactions. If you want some information, take a look at the action mapped to the route "dbal-transactions".
	- If you are using NativeQuery objects to collect scalar information (such as information for a report), please, be clean and put all mapping and queries into a service.
	- This one is important: you don't have to use Doctrine if you don't want to! (but you should at least try to learn it, it comes bundled with symfony for a reason).
	- If your team and project allows it, consider separating database logic from application login using stored procedures in your databse. Of course, persisting entities is fast and easy through PHP code (so is deleting or updating entities) but the fact of the matter is that to achieve true "separation of concerns" (something we all have heard or read of) you must separate your platform flow from database specific code. This also allows you to work with database specialists and focus on coding your application logic (is it me or are the lines blurry everywhere?).
	- Conversedly, doctrine is fast (and even fun) for quick prototyping. The symfony console will let you work with entities effortlessly (it will even create forms for your entities) and that's the kind of heavy lifting you want to avoid when prototyping.
	- When using NativeQuery objects, try not to add your parameters through variable interpolation ("CALL my_procedure($myparam1, $myparam2)") but use placeholders instead ("CALL my_procedure(?,?)") and later set the parameters through setParameter(). Variable interpolation is the thing SQLInjections and nightmares are made of. Best get used to avoiding those.

## Creating custom services

//TODO

## Forms

//TODO

## Security and users.

//TODO.

## Creating console commands.
