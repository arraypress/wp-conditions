<?php
/**
 * User-agent option lists.
 *
 * The names mirror what arraypress/wp-user-agent-utils returns from
 * UserAgent::browser(), UserAgent::os() and Request::device_type(), so a saved
 * rule value compares equal to the detected value. Keep the two in step.
 *
 * @package ArrayPress\Conditions\Options
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Options;

class UserAgents {

	/**
	 * Device types as classified by the user-agent library.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function get_device_types(): array {
		return [
			[
				'value' => 'desktop',
				'label' => __( 'Desktop', 'arraypress' ),
			],
			[
				'value' => 'mobile',
				'label' => __( 'Mobile', 'arraypress' ),
			],
			[
				'value' => 'tablet',
				'label' => __( 'Tablet', 'arraypress' ),
			],
			[
				'value' => 'bot',
				'label' => __( 'Bot', 'arraypress' ),
			],
			[
				'value' => 'unknown',
				'label' => __( 'Unknown', 'arraypress' ),
			],
		];
	}

	/**
	 * Browser names as detected by the user-agent library.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function get_browsers(): array {
		return self::to_options(
			[
				'Chrome',
				'Chrome Mobile',
				'Chrome iOS',
				'Chrome OS',
				'Safari',
				'Safari Mobile',
				'Firefox',
				'Firefox iOS',
				'Edge',
				'Opera',
				'Brave',
				'Vivaldi',
				'Samsung Browser',
				'UC Browser',
				'DuckDuckGo iOS',
				'Internet Explorer',
				'Android WebView',
				'iOS WebView',
				'Electron',
			]
		);
	}

	/**
	 * Operating system names as detected by the user-agent library.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function get_operating_systems(): array {
		return self::to_options(
			[
				'Windows 10/11',
				'Windows 8.1',
				'Windows 8',
				'Windows 7',
				'Windows',
				'macOS',
				'iOS',
				'Android',
				'Chrome OS',
				'Ubuntu',
				'Linux',
			]
		);
	}

	/**
	 * Brand names are not translated; the value is the label.
	 *
	 * @param string[] $names Names in display order.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	private static function to_options( array $names ): array {
		return array_map(
			static fn( string $name ): array => [
				'value' => $name,
				'label' => $name,
			],
			$names
		);
	}
}
