# WordPress Conditions

A flexible conditions/rules engine for WordPress with admin UI, REST API search, and extensible condition types.

## Features

- **Custom Post Type Storage** - Each condition set creates a CPT for storing rules
- **Flexible Registration** - Register conditions via config arrays, classes, or built-in presets
- **Rich Admin UI** - jQuery + Select2 powered interface with nested groups
- **REST API Search** - Built-in endpoints for posts, terms, and users with automatic whitelisting
- **Type-Aware Comparison** - Smart operators for text, numbers, arrays, dates, and more
- **AND/OR Logic** - Groups use AND logic internally, OR logic between groups

## Requirements

- PHP 8.0+
- WordPress 5.9+

## Installation

```bash
composer require arraypress/wp-conditions
```

This will also install the required dependency:

- `arraypress/wp-composer-assets` - For loading assets from Composer packages

### Select2 Setup

The library requires Select2 4.1.0. Download and place in the assets folder:

```bash
# Option 1: Download directly
curl -o assets/css/select2.min.css https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
curl -o assets/js/select2.min.js https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js

# Option 2: Via npm
npm install select2@4.1.0-rc.0
cp node_modules/select2/dist/css/select2.min.css vendor/arraypress/wp-conditions/assets/css/
cp node_modules/select2/dist/js/select2.min.js vendor/arraypress/wp-conditions/assets/js/
```

## Quick Start

```php
// Register a condition set with conditions
register_conditions( 'fraud_rule', [
    'labels' => [
        'singular' => 'Fraud Rule',
        'plural'   => 'Fraud Rules',
    ],
    'menu_icon' => 'dashicons-shield',
    'conditions' => [
        // Config-based condition
        'order_total' => [
            'label' => 'Order Total',
            'group' => 'Order',
            'type'  => 'number',
            'arg'   => 'order_total',
        ],
        
        // Built-in conditions
        'user_role',
        'day_of_week',
        'is_logged_in',
        
        // Class-based condition
        My_Custom_Condition::class,
    ],
] );

// Check conditions at runtime
$result = check_conditions( 'fraud_rule', [
    'order_total' => 150.00,
    'user_id'     => get_current_user_id(),
] );

if ( $result->matched() ) {
    $rule = $result->get_rule();
    echo "Blocked by: " . $rule->post_title;
}
```

## Field Types

| Type          | Description       | Operators                                       |
|---------------|-------------------|-------------------------------------------------|
| `text`        | Text input        | ==, !=, contains, starts_with, ends_with, empty |
| `number`      | Number input      | ==, !=, >, <, >=, <=                            |
| `number_unit` | Number + dropdown | ==, !=, >, <, >=, <=                            |
| `select`      | Static dropdown   | Single: ==, != / Multiple: any, none, all       |
| `post`        | AJAX post search  | Single: ==, != / Multiple: any, none, all       |
| `term`        | AJAX term search  | Single: ==, != / Multiple: any, none, all       |
| `user`        | AJAX user search  | Single: ==, != / Multiple: any, none, all       |
| `date`        | Date picker       | ==, !=, >, <, >=, <=                            |
| `time`        | Time picker       | ==, !=, >, <                                    |
| `boolean`     | Yes/No            | yes, no                                         |

## Built-in Conditions

### Date & Time

- `day_of_week` - Current day of the week
- `current_month` - Current month
- `current_date` - Current date
- `current_time` - Current time

### User

- `user_role` - Current user's role(s)
- `is_logged_in` - Whether user is logged in
- `user_id` - Specific user

### Post

- `post_status` - Post status
- `post_type` - Post type
- `post_author` - Post author

## Config-Based Conditions

```php
register_condition( 'my_set', 'cart_total', [
    'label'    => 'Cart Total',
    'group'    => 'Cart',
    'type'     => 'number',
    'arg'      => 'cart_total',  // Gets value from $args['cart_total']
] );

// With computed value
register_condition( 'my_set', 'customer_orders', [
    'label'         => 'Customer Order Count',
    'group'         => 'Customer',
    'type'          => 'number',
    'required_args' => ['user_id'],
    'compare_value' => fn( $args ) => wc_get_customer_order_count( $args['user_id'] ),
] );

// With AJAX search
register_condition( 'my_set', 'contains_product', [
    'label'     => 'Contains Product',
    'group'     => 'Cart',
    'type'      => 'post',
    'post_type' => 'product',
    'multiple'  => true,
    'arg'       => 'cart_product_ids',
] );

// Number with unit
register_condition( 'my_set', 'total_spent', [
    'label'         => 'Total Spent',
    'group'         => 'Customer',
    'type'          => 'number_unit',
    'units'         => [
        [ 'value' => 'all_time', 'label' => 'All Time' ],
        [ 'value' => 'this_year', 'label' => 'This Year' ],
        [ 'value' => 'this_month', 'label' => 'This Month' ],
    ],
    'required_args' => ['user_id'],
    'compare_value' => function( $args ) {
        $unit = $args['_unit'] ?? 'all_time';
        // Return value based on unit...
    },
] );
```

## Class-Based Conditions

```php
use ArrayPress\Conditions\Condition;

class IP_Address_Condition extends Condition {
    
    protected string $name = 'ip_address';
    protected string $label = 'IP Address';
    protected string $group = 'Customer';
    protected string $type = 'text';
    protected string $arg = 'ip_address';
    
    // Custom operators
    public function get_operators(): array {
        return [
            '==' => 'Is',
            '!=' => 'Is not',
            'in_range' => 'Is in CIDR range',
        ];
    }
    
    // Custom comparison logic
    public function compare( string $operator, mixed $user_value, mixed $compare_value ): bool {
        if ( $operator === 'in_range' ) {
            return $this->ip_in_cidr( $compare_value, $user_value );
        }
        
        return parent::compare( $operator, $user_value, $compare_value );
    }
    
    private function ip_in_cidr( string $ip, string $cidr ): bool {
        // CIDR matching logic...
    }
}

// Register
register_condition( 'fraud_rule', IP_Address_Condition::class );
```

## Checking Conditions

```php
// First match only
$result = check_conditions( 'fraud_rule', [
    'order_total' => 150.00,
    'ip_address'  => '192.168.1.1',
    'user_id'     => get_current_user_id(),
] );

if ( $result->matched() ) {
    $rule_id = $result->get_rule_id();
    $rule_title = $result->get_rule_title();
    $matched_group = $result->get_matched_group();
}

// All matches
$results = check_all_conditions( 'discount_rule', $args );

if ( $results->has_matches() ) {
    foreach ( $results as $match ) {
        echo $match->get_rule_title();
    }
    
    // Or get all IDs/titles
    $ids = $results->get_rule_ids();
    $titles = $results->get_rule_titles();
}
```

## Required Arguments

Conditions can declare required arguments. If not provided, the condition is skipped (not failed):

```php
register_condition( 'my_set', 'customer_orders', [
    'label'         => 'Customer Order Count',
    'type'          => 'number',
    'required_args' => ['user_id'],  // Skipped for guest users
    'compare_value' => fn( $args ) => wc_get_customer_order_count( $args['user_id'] ),
] );
```

## License

GPL-2.0-or-later
