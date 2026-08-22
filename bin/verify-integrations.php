<?php
/**
 * Check the integrations against the real plugins they wrap.
 *
 * Every helper in src/Integrations calls into EDD or WooCommerce -- methods on
 * WC_Product, functions like edd_get_discounts. PHP does not check any of those
 * until the day the closure runs, and the unit tests cannot either: they run
 * against stubs, so a stub and the integration can happily agree on a method
 * the plugin has never had. This script closes that gap by reading the
 * plugins' own source.
 *
 * It parses rather than boots: no WordPress, no database.
 *
 * Usage:
 *   php bin/verify-integrations.php
 *   php bin/verify-integrations.php --woocommerce=/path/to/woocommerce
 *   php bin/verify-integrations.php --edd=/path/to/easy-digital-downloads
 *
 * Paths default to siblings in the same plugins directory. An integration whose
 * plugin is not installed is skipped with a note rather than failing, so this
 * is useful on a machine that only has one of them.
 *
 * Exits non-zero when anything is missing, so it can gate a release -- but not
 * CI, which has neither plugin to read.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

$plugins = dirname( __DIR__, 4 );

$integrations = [
	'WooCommerce' => [
		'src'        => dirname( __DIR__ ) . '/src/Integrations/WooCommerce',
		'path'       => $plugins . '/woocommerce',
		'option'     => 'woocommerce',
		// Local variable name => the class it holds.
		'variables'  => [
			'$product'   => 'WC_Product',
			'$order'     => 'WC_Order',
			'$cart'      => 'WC_Cart',
			'$customer'  => 'WC_Customer',
			'$item'      => 'WC_Order_Item_Product',
			'$method'    => 'WC_Order_Item_Shipping',
			'$variation' => 'WC_Product_Variation',
		],
		'functions'  => '/\b((?:wc_|woocommerce_)\w+|get_woocommerce_\w+)\s*\(/i',
		'statics'    => '/\\\\?(WC_\w+)::(\w+)\s*\(/',
		'options'    => "/get_option\(\s*'(woocommerce_\w+)'/",
	],
	'EDD'         => [
		'src'        => dirname( __DIR__ ) . '/src/Integrations/EDD',
		'path'       => $plugins . '/easy-digital-downloads',
		'option'     => 'edd',
		'variables'  => [
			'$discount' => 'EDD_Discount',
			'$customer' => 'EDD_Customer',
		],
		'functions'  => '/\b(edd_\w+)\s*\(/i',
		'statics'    => '/\\\\?(EDD_\w+)::(\w+)\s*\(/',
		'options'    => "/get_option\(\s*'(edd_\w+)'/",
	],
];

// Command-line overrides for either path.
foreach ( array_slice( $argv, 1 ) as $argument ) {
	foreach ( $integrations as $name => $config ) {
		$flag = '--' . $config['option'] . '=';

		if ( str_starts_with( $argument, $flag ) ) {
			$integrations[ $name ]['path'] = substr( $argument, strlen( $flag ) );
		}
	}
}

/**
 * Every PHP file under a directory.
 *
 * @param string $dir Directory to walk.
 *
 * @return string[]
 */
function wpc_php_files( string $dir ): array {
	$files = [];

	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ) as $file ) {
		if ( 'php' === $file->getExtension() ) {
			$files[] = $file->getPathname();
		}
	}

	return $files;
}

/**
 * Index every class, its parent, and the methods it declares.
 *
 * @param string[] $files Files to scan.
 *
 * @return array{declared: array, extends: array, functions: array}
 */
function wpc_index( array $files ): array {
	$declared  = [];
	$extends   = [];
	$functions = [];

	foreach ( $files as $file ) {
		$code = file_get_contents( $file );

		preg_match_all( '/^\s*function\s+(\w+)\s*\(/mi', $code, $global );

		foreach ( $global[1] as $function ) {
			$functions[ strtolower( $function ) ] = true;
		}

		if ( ! preg_match_all(
			'/^\s*(?:abstract\s+|final\s+)?class\s+(\w+)(?:\s+extends\s+(\\\\?[\w\\\\]+))?/mi',
			$code,
			$classes,
			PREG_OFFSET_CAPTURE | PREG_SET_ORDER
		) ) {
			continue;
		}

		foreach ( $classes as $i => $class ) {
			$name  = $class[1][0];
			$start = $class[0][1];
			$end   = $classes[ $i + 1 ][0][1] ?? strlen( $code );
			$body  = substr( $code, $start, $end - $start );

			if ( ! empty( $class[2][0] ) ) {
				$extends[ $name ] = ltrim( $class[2][0], '\\' );
			}

			preg_match_all( '/function\s+(\w+)\s*\(/i', $body, $methods );

			foreach ( $methods[1] as $method ) {
				$declared[ $name ][ $method ] = true;
			}
		}
	}

	return [
		'declared'  => $declared,
		'extends'   => $extends,
		'functions' => $functions,
	];
}

/**
 * Whether a class or any ancestor declares a method.
 *
 * @param array  $index  The plugin index.
 * @param string $class  Class name.
 * @param string $method Method name.
 *
 * @return bool
 */
function wpc_has_method( array $index, string $class, string $method ): bool {
	if ( isset( $index['declared'][ $class ][ $method ] ) ) {
		return true;
	}

	if ( ! isset( $index['extends'][ $class ] ) ) {
		return false;
	}

	return wpc_has_method( $index, $index['extends'][ $class ], $method );
}

$problems      = [];
$optional_refs = [];
$checked       = 0;
$reports       = [];

foreach ( $integrations as $name => $config ) {
	if ( ! is_dir( $config['src'] ) ) {
		continue;
	}

	if ( ! is_dir( $config['path'] ) ) {
		$reports[] = "$name: skipped, not installed at {$config['path']}";
		continue;
	}

	$files = wpc_php_files( $config['path'] );
	$index = wpc_index( $files );
	$count = 0;

	$source = '';

	foreach ( glob( $config['src'] . '/*.php' ) as $file ) {
		$code    = file_get_contents( $file );
		$short   = basename( $file );
		$source .= $code;

		// Instance calls on a variable we can name a class for.
		preg_match_all( '/(\$\w+)\??->(\w+)\s*\(/', $code, $calls, PREG_SET_ORDER );

		foreach ( $calls as $call ) {
			[ , $variable, $method ] = $call;

			if ( ! isset( $config['variables'][ $variable ] ) ) {
				continue;
			}

			++$count;

			if ( ! wpc_has_method( $index, $config['variables'][ $variable ], $method ) ) {
				$problems[] = "$name/$short: {$config['variables'][ $variable ]}::$method() does not exist";
			}
		}

		// Names the file itself guards. A guarded reference that is missing is an
		// add-on this machine does not have, not a mistake -- but it is still
		// counted and reported, because silently skipping it would hide a typo
		// in the guard itself.
		preg_match_all( "/(?:class_exists|function_exists|method_exists)\(\s*'([\\\\\w]+)'/", $code, $guarded );

		$optional = array_flip( array_map(
			static fn( string $guard ): string => strtolower( ltrim( $guard, '\\\\' ) ),
			$guarded[1]
		) );

		// Static calls on the plugin's own classes.
		preg_match_all( $config['statics'], $code, $statics, PREG_SET_ORDER );

		foreach ( $statics as $static ) {
			[ , $class, $method ] = $static;

			++$count;

			if ( wpc_has_method( $index, $class, $method ) ) {
				continue;
			}

			if ( isset( $optional[ strtolower( $class ) ] ) ) {
				$optional_refs[] = "$name/$short: $class (guarded add-on)";
				continue;
			}

			$problems[] = "$name/$short: $class::$method() does not exist";
		}

		// Global functions. A constructor is not one, so `new Foo(` is skipped.
		preg_match_all( $config['functions'], $code, $globals, PREG_OFFSET_CAPTURE );

		$seen = [];

		foreach ( $globals[1] as $global ) {
			[ $function, $offset ] = $global;
			$key                   = strtolower( $function );

			if ( isset( $seen[ $key ] ) ) {
				continue;
			}

			$seen[ $key ] = true;

			// `new EDD_Stats(` and `new \WC_Tax(` are instantiations.
			if ( preg_match( '/new\s+\\\\?$/', substr( $code, 0, $offset ) ) ) {
				continue;
			}

			++$count;

			if ( isset( $index['functions'][ $key ] ) ) {
				continue;
			}

			if ( isset( $optional[ $key ] ) ) {
				$optional_refs[] = "$name/$short: $function() (guarded add-on)";
				continue;
			}

			$problems[] = "$name/$short: function $function() does not exist";
		}
	}

	// Options read straight from the database.
	preg_match_all( $config['options'], $source, $options );

	if ( ! empty( $options[1] ) ) {
		$plugin_source = '';

		foreach ( $files as $file ) {
			$plugin_source .= file_get_contents( $file );
		}

		foreach ( array_unique( $options[1] ) as $option ) {
			++$count;

			if ( ! str_contains( $plugin_source, $option ) ) {
				$problems[] = "$name: option $option is not referenced anywhere in " . basename( $config['path'] );
			}
		}
	}

	$checked  += $count;
	$reports[] = "$name: $count calls checked against " . basename( $config['path'] );
}

/**
 * Every Helper::method() a condition names must exist on the helper class.
 *
 * This half needs no plugin at all: it checks the library against itself. A
 * condition is a closure over a helper method, and PHP will not notice a
 * renamed or deleted one until the closure runs -- which, for a rule that
 * blocks checkouts, is on somebody's purchase.
 */
$conditions_root = dirname( __DIR__ ) . '/src';

foreach ( [ 'EDD', 'WooCommerce' ] as $integration ) {
	$helper_dir    = "$conditions_root/Integrations/$integration";
	$condition_dir = "$conditions_root/Conditions/$integration";

	if ( ! is_dir( $helper_dir ) || ! is_dir( $condition_dir ) ) {
		continue;
	}

	$methods = [];

	foreach ( glob( $helper_dir . '/*.php' ) as $file ) {
		$class = basename( $file, '.php' );

		preg_match_all(
			'/(?:public|private|protected)\s+static\s+function\s+(\w+)/',
			file_get_contents( $file ),
			$declared_methods
		);

		foreach ( $declared_methods[1] as $method ) {
			$methods[ $class ][ $method ] = true;
		}
	}

	$count = 0;

	foreach ( glob( $condition_dir . '/*.php' ) as $file ) {
		$code  = file_get_contents( $file );
		$short = basename( $file );

		// Which local name refers to which helper class, honouring `as` aliases.
		preg_match_all(
			'/use\s+ArrayPress\\\\Conditions\\\\Integrations\\\\' . $integration . '\\\\(\w+)(?:\s+as\s+(\w+))?;/',
			$code,
			$uses,
			PREG_SET_ORDER
		);

		$aliases = [];

		foreach ( $uses as $use ) {
			$aliases[ ( $use[2] ?? '' ) ?: $use[1] ] = $use[1];
		}

		preg_match_all( '/\b(\w+)::(\w+)\s*\(/', $code, $calls, PREG_SET_ORDER );

		foreach ( $calls as $call ) {
			[ , $local, $method ] = $call;

			if ( ! isset( $aliases[ $local ] ) ) {
				continue;
			}

			$class = $aliases[ $local ];
			++$count;

			if ( ! isset( $methods[ $class ][ $method ] ) ) {
				$problems[] = "$integration/$short: condition calls $class::$method(), which does not exist";
			}
		}
	}

	$checked  += $count;
	$reports[] = "$integration: $count helper calls named by conditions";
}

foreach ( $reports as $report ) {
	echo "  $report\n";
}

$optional_refs = array_values( array_unique( $optional_refs ) );

if ( ! empty( $optional_refs ) ) {
	echo "\n" . count( $optional_refs ) . " guarded reference(s) to add-ons not installed here:\n";

	foreach ( $optional_refs as $reference ) {
		echo "  . $reference\n";
	}
}

$problems = array_values( array_unique( $problems ) );

if ( empty( $problems ) ) {
	echo "\nVerified $checked calls.\n";
	exit( 0 );
}

echo "\n" . count( $problems ) . " problem(s) found:\n";

foreach ( $problems as $problem ) {
	echo "  - $problem\n";
}

exit( 1 );
