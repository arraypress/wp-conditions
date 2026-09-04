<?php
/**
 * The user-agent option lists must name what the library actually detects.
 *
 * @package ArrayPress\Conditions\Tests
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Options\UserAgents;
use ArrayPress\UserAgentUtils\UserAgent;
use PHPUnit\Framework\TestCase;

final class UserAgentOptionsTest extends TestCase {

	private const CHROME_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';
	private const SAFARI_MAC     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15';
	private const FIREFOX_LINUX  = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:127.0) Gecko/20100101 Firefox/127.0';
	private const EDGE_WINDOWS   = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0';
	private const SAFARI_IPHONE  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';
	private const CHROME_ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36';
	private const SAFARI_IPAD    = 'Mozilla/5.0 (iPad; CPU OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';
	private const GOOGLEBOT      = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

	/**
	 * @return string[]
	 */
	private static function values( array $options ): array {
		return array_column( $options, 'value' );
	}

	public function test_option_values_are_unique_and_non_empty(): void {
		foreach ( [ UserAgents::get_device_types(), UserAgents::get_browsers(), UserAgents::get_operating_systems() ] as $options ) {
			$values = self::values( $options );
			$this->assertNotContains( '', $values );
			$this->assertSame( array_values( array_unique( $values ) ), $values );
		}
	}

	public function test_detected_browsers_are_offered_as_options(): void {
		$values = self::values( UserAgents::get_browsers() );

		foreach ( [ self::CHROME_WINDOWS, self::SAFARI_MAC, self::FIREFOX_LINUX, self::EDGE_WINDOWS, self::SAFARI_IPHONE, self::CHROME_ANDROID ] as $agent ) {
			$browser = UserAgent::browser( $agent );
			$this->assertNotNull( $browser, $agent );
			$this->assertContains( $browser, $values, $agent );
		}
	}

	public function test_detected_operating_systems_are_offered_as_options(): void {
		$values = self::values( UserAgents::get_operating_systems() );

		foreach ( [ self::CHROME_WINDOWS, self::SAFARI_MAC, self::FIREFOX_LINUX, self::SAFARI_IPHONE, self::CHROME_ANDROID ] as $agent ) {
			$os = UserAgent::os( $agent );
			$this->assertNotNull( $os, $agent );
			$this->assertContains( $os, $values, $agent );
		}
	}

	public function test_device_types_cover_every_classification(): void {
		$values = self::values( UserAgents::get_device_types() );

		$this->assertSame( 'desktop', UserAgent::device_type( self::CHROME_WINDOWS ) );
		$this->assertSame( 'mobile', UserAgent::device_type( self::SAFARI_IPHONE ) );
		$this->assertSame( 'tablet', UserAgent::device_type( self::SAFARI_IPAD ) );
		$this->assertSame( 'bot', UserAgent::device_type( self::GOOGLEBOT ) );
		$this->assertSame( 'unknown', UserAgent::device_type( '' ) );

		foreach ( [ self::CHROME_WINDOWS, self::SAFARI_IPHONE, self::SAFARI_IPAD, self::GOOGLEBOT, '' ] as $agent ) {
			$this->assertContains( UserAgent::device_type( $agent ), $values );
		}
	}
}
