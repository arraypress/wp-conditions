# Conditions

Let a shop owner build a rule — "logged-in customers in the UK, on a
weekend" — from an admin screen, and check it in code.

## What it does

Anything with rules ends up needing this: a discount that applies to some
carts, a fee that applies to some countries, a notice shown to some users.
Hard-coding them means a release every time one changes; a settings field
means parsing whatever the owner typed.

This gives the owner a builder — pick a condition, an operator, a value, and
group them with any/all — and gives you one call that answers true or false.

## Features

* Register a set of rules with its own admin screen, from one call
* Give owners built-in conditions — user role, country, day of week, cart total
* Add a condition of your own, with its own operators and values
* Group rules so any or all of them must match
* Search for posts, terms and users inside a rule, rather than typing ids
* Check a set from your own code, against whatever context you have
* Ask which rules matched, when the answer needs explaining

## Installation

```bash
composer require arraypress/wp-conditions
```

## Quick start

Register the set, and the screen that edits it:

```php
add_action( 'init', function () {
	register_conditions( 'discount_rule', [
		'labels'     => [
			'singular' => 'Discount Rule',
			'plural'   => 'Discount Rules',
		],
		'menu_icon'  => 'dashicons-tag',
		'conditions' => [ 'user_role', 'day_of_week', 'is_logged_in' ],
	] );
} );
```

Then, at the point the rule matters:

```php
if ( check_conditions( 'discount_rule', [ 'user_id' => get_current_user_id() ] ) ) {
	// ...
}
```

`check_conditions()` is true when any rule set matches;
`check_all_conditions()` requires all of them.

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
