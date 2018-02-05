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

