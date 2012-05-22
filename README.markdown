## Navigation Module for Bonfire

This is a simple Navigation Module for the Bonfire (http://cibonfire.com/) application.

## Install

- Drop the module into the bonfire/modules folder
- Run the module Install migration
- Add the permissions to the required roles
- Add the helper call to the theme
- That's it

##Features

- Navigation Groups
- Simple Helper to call from the theme template
- Editable in the Content context inside Bonfire

## Changes
- Added setting to wrap list items in a span for for css styling
- Added setting to change the name of the current active class, defaults to current
- Adding support for Bonfire 0.6 (Twitter Bootstrap) - major thanks to @jfox015 and @svizion

## Know Issues
- Drag and Drop ordering is not working in this version yet

## Helper

The navigation_helper is very simple to use.

		$attributes['id']     = 'nav';
		$attributes['class']  = 'dropdown dropdown-horizontal';

		$attributes['active'] = 'active'; 
		$attributes['wrap']   = true;

		echo show_navigation('header', TRUE, $attributes);

In this case "header" is the Navigation group defined in the Bonfire admin.
"TRUE" tells the helper to display child navigation items.
"$attributes" is an array applied to the main div

## Documentation

More documentation can be found here http://sean.downey.ie/docs/bonfire-navigation/


- [Log Issues or Suggestions](https://github.com/seandowney/bonfire_navigationmodule/issues)
- [Follow me on Twitter](http://twitter.com/downey_sean)

