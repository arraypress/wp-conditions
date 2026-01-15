# WP Conditions

A flexible conditions/rules engine for WordPress with an admin UI, REST API search, and extensible condition types. Build powerful rule-based systems for discounts, fraud detection, access control, content display, and more.

## Features

- **Visual Rule Builder** - Intuitive admin UI with AND/OR logic groups
- **Multiple Field Types** - Text, number, select, posts, terms, users, dates, and more
- **Built-in Conditions** - Ready-to-use conditions for users, posts, dates, requests, and EDD
- **Extensible** - Create custom conditions via arrays, classes, or callbacks
- **REST API Search** - AJAX-powered search for posts, terms, and users
- **Type-Safe Comparisons** - Proper comparison logic for each field type

## Requirements

- PHP 8.0+
- WordPress 6.0+

## Installation

Install via Composer:

```bash
composer require arraypress/wp-conditions
```

## Quick Start

### 1. Register a Condition Set

```php
use function ArrayPress\Conditions\register_conditions;

add_action( 'init', function() {
    register_conditions( 'discount_rule', [
        'labels' => [
            'singular' => 'Discount Rule',
            'plural'   => 'Discount Rules',
        ],
        'menu_icon'   => 'dashicons-tag',
        'menu_parent' => 'edit.php?post_type=shop_order', // Optional: nest under a menu
        'conditions'  => [
            // Built-in conditions
            'user_role',
            'day_of_week',
            'is_logged_in',
            
            // Custom condition
            'cart_total' => [
                'label'       => 'Cart Total',
                'group'       => 'Cart',
                'type'        => 'number',
                'placeholder' => 'e.g. 100.00',
                'arg'         => 'cart_total',
            ],
        ],
    ] );
} );
```

### 2. Check Conditions

```php
use function ArrayPress\Conditions\check_conditions;

// Check and get first matching rule
$result = check_conditions( 'discount_rule', [
    'cart_total' => 150.00,
    'user_id'    => get_current_user_id(),
] );

if ( $result->matched() ) {
    $rule     = $result->get_rule();
    $discount = $result->get_rule_meta( '_discount_amount' );
    
    apply_discount( $discount );
}
```

### 3. Check All Matching Rules

```php
use function ArrayPress\Conditions\check_all_conditions;

$results = check_all_conditions( 'discount_rule', [
    'cart_total' => 200.00,
] );

if ( $results->has_matches() ) {
    foreach ( $results as $match ) {
        echo "Matched: " . $match->get_rule_title() . "\n";
    }
}
```

## Condition Set Configuration

### Full Options

```php
register_conditions( 'my_rules', [
    'labels' => [
        'singular' => 'My Rule',
        'plural'   => 'My Rules',
    ],
    'menu_icon'    => 'dashicons-shield',      // Dashicon or URL
    'menu_parent'  => 'tools.php',             // Parent menu slug (optional)
    'show_in_menu' => true,                    // Show in admin menu
    'capability'   => 'manage_options',        // Required capability
    'description'  => 'Configure rule conditions.', // Meta box description
    'conditions'   => [],                      // Array of conditions
] );
```

## Field Types

### Text

Basic text input with string comparison operators.

```php
'email_domain' => [
    'label'       => 'Email Domain',
    'group'       => 'Customer',
    'type'        => 'text',
    'placeholder' => 'e.g. gmail.com',
    'description' => 'Match against the customer email domain.',
    'arg'         => 'email_domain',
],
```

**Operators:** Equals, Does not equal, Contains, Does not contain, Starts with, Ends with, Is empty, Is not empty

---

### Number

Numeric input with mathematical comparison operators.

```php
'order_total' => [
    'label'       => 'Order Total',
    'group'       => 'Order',
    'type'        => 'number',
    'placeholder' => 'e.g. 100.00',
    'min'         => 0,
    'max'         => 10000,
    'step'        => 0.01,
    'arg'         => 'order_total',
],
```

**Operators:** Equal to, Not equal to, Greater than, Less than, Greater or equal to, Less or equal to

---

### Number with Unit

Numeric input paired with a unit selector. Useful for time periods, measurements, etc.

```php
'account_age' => [
    'label'       => 'Account Age',
    'group'       => 'Customer',
    'type'        => 'number_unit',
    'placeholder' => 'e.g. 30',
    'min'         => 0,
    'units'       => [
        [ 'value' => 'day', 'label' => 'Day(s)' ],
        [ 'value' => 'week', 'label' => 'Week(s)' ],
        [ 'value' => 'month', 'label' => 'Month(s)' ],
        [ 'value' => 'year', 'label' => 'Year(s)' ],
    ],
    'compare_value' => function( $args ) {
        // Access unit via $args['_unit'] and number via $args['_number']
        $unit = $args['_unit'] ?? 'day';
        return get_account_age_in_unit( $args['user_id'], $unit );
    },
],
```

**Operators:** Equal to, Not equal to, Greater than, Less than, Greater or equal to, Less or equal to

---

### Select (Single)

Dropdown select for choosing one option.

```php
'order_status' => [
    'label'       => 'Order Status',
    'group'       => 'Order',
    'type'        => 'select',
    'multiple'    => false,
    'placeholder' => 'Select status...',
    'options'     => [
        [ 'value' => 'pending', 'label' => 'Pending' ],
        [ 'value' => 'processing', 'label' => 'Processing' ],
        [ 'value' => 'completed', 'label' => 'Completed' ],
        [ 'value' => 'refunded', 'label' => 'Refunded' ],
    ],
    'arg' => 'order_status',
],
```

**Operators:** Is, Is not

---

### Select (Multiple)

Multi-select dropdown for choosing multiple options.

```php
'payment_gateway' => [
    'label'       => 'Payment Gateway',
    'group'       => 'Order',
    'type'        => 'select',
    'multiple'    => true,
    'placeholder' => 'Select gateways...',
    'options'     => fn() => get_payment_gateway_options(), // Callback supported
    'arg'         => 'payment_gateway',
],
```

**Operators:** Is any of, Is none of, Is all of

---

### Boolean

Yes/No toggle for true/false conditions.

```php
'is_first_order' => [
    'label'         => 'Is First Order',
    'group'         => 'Customer',
    'type'          => 'boolean',
    'description'   => 'Check if this is the customer\'s first order.',
    'compare_value' => fn( $args ) => is_first_order( $args['customer_id'] ),
],
```

**Operators:** Yes, No

---

### Post

Search and select posts of a specific type.

```php
'purchased_products' => [
    'label'       => 'Purchased Products',
    'group'       => 'Customer',
    'type'        => 'post',
    'post_type'   => 'product',     // Required: post type to search
    'multiple'    => true,
    'placeholder' => 'Search products...',
    'compare_value' => fn( $args ) => get_customer_product_ids( $args['customer_id'] ),
],
```

**Operators (single):** Is, Is not  
**Operators (multiple):** Contains any of, Contains none of, Contains all of

---

### Term

Search and select taxonomy terms.

```php
'product_categories' => [
    'label'       => 'Product Categories',
    'group'       => 'Cart',
    'type'        => 'term',
    'taxonomy'    => 'product_cat',  // Required: taxonomy to search
    'multiple'    => true,
    'placeholder' => 'Search categories...',
    'compare_value' => fn( $args ) => get_cart_category_ids(),
],
```

**Operators (single):** Is, Is not  
**Operators (multiple):** Contains any of, Contains none of, Contains all of

---

### User

Search and select users, optionally filtered by role.

```php
'assigned_agent' => [
    'label'       => 'Assigned Agent',
    'group'       => 'Ticket',
    'type'        => 'user',
    'role'        => 'support_agent',  // Optional: filter by role(s)
    'multiple'    => true,
    'placeholder' => 'Search agents...',
    'arg'         => 'assigned_agent_id',
],
```

**Operators (single):** Is, Is not  
**Operators (multiple):** Contains any of, Contains none of, Contains all of

---

### Date

Date picker for date comparisons.

```php
'order_date' => [
    'label'         => 'Order Date',
    'group'         => 'Order',
    'type'          => 'date',
    'description'   => 'The date the order was placed.',
    'compare_value' => fn( $args ) => get_order_date( $args['order_id'] ),
],
```

**Operators:** Is, Is not, Is after, Is before, Is on or after, Is on or before

---

### Time

Time picker for time-of-day comparisons.

```php
'current_time' => [
    'label'         => 'Current Time',
    'group'         => 'Date & Time',
    'type'          => 'time',
    'description'   => 'The current time of day.',
    'compare_value' => fn( $args ) => current_time( 'H:i' ),
],
```

**Operators:** Is, Is not, Is after, Is before

---

### Tags

User-created tags for flexible pattern matching. Great for domains, extensions, prefixes, etc.

```php
'email_domain' => [
    'label'       => 'Email Domain',
    'group'       => 'Customer',
    'type'        => 'tags',
    'placeholder' => 'Type domain and press Enter...',
    'description' => 'Match if email ends with any of these domains.',
    'operators'   => [
        'any_ends'  => 'Ends with any of',
        'none_ends' => 'Ends with none of',
    ],
    'compare_value' => fn( $args ) => $args['customer_email'],
],
```

**Operator Sets:**
- **Suffix matching:** Ends with any of, Ends with none of
- **Prefix matching:** Starts with any of, Starts with none of
- **Contains matching:** Contains any of, Contains none of
- **Exact matching:** Is any of, Is none of

---

### IP Address

IP address matching with support for exact, CIDR notation, and wildcards.

```php
'customer_ip' => [
    'label'       => 'Customer IP',
    'group'       => 'Request',
    'type'        => 'ip',
    'placeholder' => 'e.g. 192.168.1.0/24 or 10.0.0.*',
    'description' => 'Match against the customer IP address.',
    'compare_value' => fn( $args ) => $args['ip_address'],
],
```

**Operators:** Matches, Does not match

**Supported Formats:**
- Exact: `192.168.1.1`
- CIDR: `192.168.1.0/24`
- Wildcard: `192.168.1.*`

---

### Email

Email address matching with pattern support.

```php
'customer_email' => [
    'label'       => 'Customer Email',
    'group'       => 'Customer',
    'type'        => 'email',
    'placeholder' => 'e.g. @gmail.com, .edu',
    'description' => 'Match customer email patterns.',
    'compare_value' => fn( $args ) => $args['email'],
],
```

**Operators:** Matches, Does not match

**Supported Patterns:**
- Full email: `john@example.com`
- Domain: `@gmail.com`
- TLD: `.edu`
- Partial domain: `example.com` (matches `@example.com` and `@sub.example.com`)

---

### AJAX

Custom AJAX-powered search for any data source.

```php
'discount_code' => [
    'label'       => 'Discount Code',
    'group'       => 'Order',
    'type'        => 'ajax',
    'multiple'    => true,
    'placeholder' => 'Search discounts...',
    'ajax'        => function( ?string $search, ?array $ids ): array {
        // Return array of [ 'value' => '...', 'label' => '...' ]
        if ( $ids ) {
            return get_discounts_by_ids( $ids );
        }
        return search_discounts( $search );
    },
    'compare_value' => fn( $args ) => $args['applied_discount_ids'],
],
```

**Operators:** Same as select (single or multiple based on `multiple` setting)

## Built-in Conditions

### User Conditions

| Condition | Description |
|-----------|-------------|
| `user_role` | Match against user role(s) |
| `is_logged_in` | Check if user is logged in |
| `user_id` | Match specific user(s) |
| `user_email` | Match user email |
| `email_domain` | Match email domain suffix |
| `user_username` | Match username |
| `user_registered` | Account age comparison |
| `user_meta` | Match user meta value |

### Date & Time Conditions

| Condition | Description |
|-----------|-------------|
| `day_of_week` | Current day (Monday-Sunday) |
| `week_of_year` | Week number (1-52) |
| `day_of_month` | Day of month (1-31) |
| `current_month` | Current month |
| `current_date` | Specific date |
| `current_time` | Time of day |

### Post Conditions

| Condition | Description |
|-----------|-------------|
| `post_status` | Post status |
| `post_type` | Post type |
| `post_author` | Post author |
| `post_age` | Time since published |
| `post_category` | Post categories |
| `post_tag` | Post tags |
| `has_term` | Has specific terms |

### Request Conditions

| Condition | Description |
|-----------|-------------|
| `current_url` | Current URL |
| `referrer` | HTTP referrer |
| `query_var` | URL query parameter |
| `accept_language` | Browser language |
| `ip_address` | Visitor IP |
| `country` | Visitor country |
| `device_type` | Mobile/Desktop/Bot |
| `browser` | Browser type |
| `operating_system` | OS type |

### WordPress Context Conditions

| Condition | Description |
|-----------|-------------|
| `is_front_page` | Is site front page |
| `is_home` | Is blog home |
| `is_single` | Is single post |
| `is_page` | Is static page |
| `is_archive` | Is archive page |
| `is_search` | Is search results |
| `is_404` | Is 404 page |
| `is_admin` | Is admin area |
| `is_ajax` | Is AJAX request |
| `is_rest` | Is REST request |
| `is_cron` | Is cron job |

### EDD Conditions

When Easy Digital Downloads is active, additional conditions are available:

**Cart:** Total, subtotal, tax, fees, quantity, products, categories, tags, discounts, bundle count, subscription count, license count, renewal count, free item count

**Customer:** Type (new/returning), order count, total spent, orders/spend in period, email, purchased products/categories/tags, account age, IP count, refund rate

**Order:** Total, subtotal, tax, discount, status, gateway, currency, products, categories, tags, country, region, city, IP, email, dates, renewal/subscription status

**Product:** Type, status, categories, tags, sales, earnings, sales/earnings in period

**Store:** Earnings in period, sales in period, refunds in period, refund rate, tax collected

**Checkout:** Selected gateway, billing country, billing region

**Commission:** Amount, rate, status, type, product, categories, tags, recipient user

**Recipient:** Total/paid/unpaid earnings, sales counts, account age, vendor status, commission rate, payout method

## Creating Custom Conditions

### Array-Based (Inline)

```php
register_conditions( 'my_rules', [
    'conditions' => [
        'cart_total' => [
            'label'         => 'Cart Total',
            'group'         => 'Cart',
            'type'          => 'number',
            'placeholder'   => 'e.g. 100.00',
            'min'           => 0,
            'step'          => 0.01,
            'description'   => 'The shopping cart total.',
            'arg'           => 'cart_total',           // Pull from args
            'required_args' => [ 'cart_total' ],       // Required args
        ],
        
        'customer_type' => [
            'label'       => 'Customer Type',
            'group'       => 'Customer',
            'type'        => 'select',
            'options'     => [
                [ 'value' => 'new', 'label' => 'New Customer' ],
                [ 'value' => 'returning', 'label' => 'Returning Customer' ],
            ],
            'compare_value' => function( $args ) {
                // Dynamic comparison value
                return has_previous_orders( $args['user_id'] ) ? 'returning' : 'new';
            },
        ],
    ],
] );
```

### Class-Based

Create reusable conditions by extending the `Condition` class:

```php
use ArrayPress\Conditions\Condition;

class Cart_Total_Condition extends Condition {

    protected string $name = 'cart_total';
    protected string $label = 'Cart Total';
    protected string $group = 'Cart';
    protected string $type = 'number';
    protected ?string $arg = 'cart_total';
    protected array $required_args = [ 'cart_total' ];

    public function get_operators(): array {
        return [
            '>'  => 'Greater than',
            '<'  => 'Less than',
            '>=' => 'Greater or equal to',
            '<=' => 'Less or equal to',
        ];
    }

    public function get_compare_value( array $args ): mixed {
        // Custom logic to get the comparison value
        return WC()->cart->get_total( 'edit' );
    }

    public function compare( string $operator, mixed $user_value, mixed $compare_value ): bool {
        // Custom comparison logic (optional - uses default if not overridden)
        return parent::compare( $operator, $user_value, $compare_value );
    }

}

// Register
register_condition( 'my_rules', Cart_Total_Condition::class );
```

### Adding Conditions to Existing Sets

```php
// Add single condition
register_condition( 'discount_rule', 'user_role' );

// Add class-based condition
register_condition( 'discount_rule', My_Custom_Condition::class );

// Add inline condition
register_condition( 'discount_rule', 'vip_customer', [
    'label'         => 'VIP Customer',
    'group'         => 'Customer',
    'type'          => 'boolean',
    'compare_value' => fn( $args ) => is_vip_customer( $args['user_id'] ),
] );
```

## Matching Conditions

### Single Match (First)

```php
$result = check_conditions( 'fraud_rule', [
    'order_total'     => 500.00,
    'billing_country' => 'US',
    'ip_address'      => '192.168.1.100',
] );

if ( $result->matched() ) {
    // Get the matched rule
    $rule = $result->get_rule();          // WP_Post object
    $id   = $result->get_rule_id();       // Post ID
    $title = $result->get_rule_title();   // Post title
    
    // Get rule meta
    $action = $result->get_rule_meta( '_fraud_action' );
    
    // Get the matched condition group
    $group = $result->get_matched_group();
}
```

### All Matches

```php
$results = check_all_conditions( 'discount_rule', [
    'cart_total' => 200.00,
    'user_id'    => 123,
] );

// Check if any matched
if ( $results->has_matches() ) {
    // Get count
    $count = $results->count();
    
    // Get all rule IDs
    $rule_ids = $results->get_rule_ids();
    
    // Get all rule titles
    $titles = $results->get_rule_titles();
    
    // Get all rule posts
    $rules = $results->get_rules();
    
    // Get first/last match
    $first = $results->get_first();
    $last  = $results->get_last();
    
    // Iterate
    foreach ( $results as $match ) {
        $discount = $match->get_rule_meta( '_discount_percent' );
        apply_discount( $discount );
    }
    
    // Filter results
    $high_priority = $results->filter( function( $match ) {
        return $match->get_rule_meta( '_priority' ) === 'high';
    } );
    
    // Map results
    $discounts = $results->map( function( $match ) {
        return $match->get_rule_meta( '_discount_amount' );
    } );
}
```

## Custom Operators

Override default operators for a condition:

```php
'priority_level' => [
    'label'     => 'Priority Level',
    'type'      => 'number',
    'operators' => [
        '>'  => 'Higher than',
        '<'  => 'Lower than',
        '==' => 'Exactly',
    ],
    'arg' => 'priority',
],
```

Use the `Operators` class for predefined sets:

```php
use ArrayPress\Conditions\Operators;

'my_condition' => [
    'operators' => Operators::numeric(),        // Numeric comparisons
    'operators' => Operators::text(),           // Text comparisons
    'operators' => Operators::boolean(),        // Yes/No
    'operators' => Operators::date(),           // Date comparisons
    'operators' => Operators::array_multiple(), // Array containment
    'operators' => Operators::tags_ends(),      // Suffix matching
    'operators' => Operators::ip(),             // IP matching
    'operators' => Operators::email(),          // Email matching
],
```

## Time Periods

Use the `Periods` class for standardized time units:

```php
use ArrayPress\Conditions\Periods;

'orders_in_period' => [
    'type'  => 'number_unit',
    'units' => Periods::get_units(),      // hour, day, week, month, year
    // or
    'units' => Periods::get_age_units(),  // day, week, month, year (no hours)
],

// Convert period to seconds
$seconds = Periods::to_seconds( 'week', 2 );  // 2 weeks in seconds

// Get date range
$range = Periods::get_date_range( 'month', 1 );
// Returns: [ 'start' => '2024-01-15 00:00:00', 'end' => '2024-02-15 23:59:59' ]
```

## Hooks and Filters

### Actions

```php
// After conditions are saved
add_action( 'save_post_{post_type}', function( $post_id, $post ) {
    $conditions = get_post_meta( $post_id, '_conditions', true );
    // Do something with saved conditions
}, 20, 2 );
```

### REST API Permissions

```php
// Customize REST API permission check
add_filter( 'conditions_rest_permission', function( $allowed ) {
    return current_user_can( 'edit_posts' );
} );
```

## How Condition Logic Works

Conditions use AND/OR logic:

- **Within a group:** All conditions must match (AND)
- **Between groups:** Any group can match (OR)

Example with 2 groups:

```
Group 1 (AND):
  - Cart Total > 100
  - User Role = wholesale

OR

Group 2 (AND):
  - Cart Total > 500
  - Is Logged In = Yes
```

This matches if:
- Cart is over $100 AND user is wholesale, OR
- Cart is over $500 AND user is logged in

## License

GPL-2.0-or-later

## Credits

Developed by [ArrayPress](https://arraypress.com/).