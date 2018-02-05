
#Refresh Symfony

A small project created to remember how Symfony works.

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

##Setting it up on GIT...

This one is easy:

	git init
	git add whatever files you need
	git commit -m 'Whatever commit message you want'
	git remote add origin url_here
	git push -u origin master

The -u is to set the tracking of your branches.

##Creating a quick controller.

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
