# Refresh Symfony

A small project created to remember how Symfony works and also serve as a reference.

It will cover the next steps:

- Creating the project and setting it up.
- Setting it up on GIT.
- Creating a quick controller.
- Using twig.
- Databases and doctrine.
- Forms.
- Security and users.

It is recommended that the steps are followed one by one since the work is incremental. The idea is that all sections can be read in order and their instructions carried out so in the end we have some basic Symfony knowledge.

## Creating the project and setting it up.

First things first, this is how you create the project.

	symfony new project_name version

For my version of PHP I need to go symfony 2.8 so...
	
	symfony new refresh_symfony 2.8

Next it would be a good idea to check the configuration by navigating to:

	localhost/refresh_symfony/web/config.php

It is likely that it will complain about the cache and log directories not being readable. Fix that, either by 0777 them or, if you are a subtle person, chowning them to the daemon user in the daemon group

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

Doctrine is huge and I can't quite see the point in many of its features (do I really need a query builder? do I really want to delegate database related code to my PHP code?... but then again, that's strictly personal). Still, that's the ORM bundled with Symfony and even if we could use another, I think of it as kind of a... default.

### Configuration

The first thing we need to do is to have a database running. In this case, with Lampp installed I am running a MariaDB database. The access parameters must be set in the app/config/parameters.yml file:

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

### More complex queries

//TODO.

### Custom repositories.

//TODO.

### Relationships

//TODO.

### Database Layer interaction.

//TODO.

## Creating custom services

//TODO

## Forms

//TODO

## Security and users.

//TODO.
