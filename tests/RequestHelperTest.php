<?php
/**
 * What the request helper believes about the request.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Helpers\Request;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestHelperTest
 */
final class RequestHelperTest extends TestCase {

	/**
	 * The server variables as they were.
	 *
	 * @var array
	 */
	private array $server;

	protected function setUp(): void {
		$this->server = $_SERVER;
	}

	protected function tearDown(): void {
		$_SERVER = $this->server;
	}

	/**
	 * The country header is believed only through a trusted proxy, and only
	 * when it is a country.
	 *
	 * It was read from anyone, so a visitor to a site not behind Cloudflare
	 * could send CF-IPCountry: US and pass a country allow-list.
	 */
	public function test_the_country_header_is_believed_only_through_a_trusted_proxy(): void {
		$_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';

		$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
		$this->assertNull( Request::get_country(), 'A header from a direct visitor was believed.' );

		// 104.16.0.0/13 is one of Cloudflare's own ranges.
		$_SERVER['REMOTE_ADDR'] = '104.16.1.1';
		$this->assertSame( 'US', Request::get_country() );

		foreach ( [ 'XX', 'T1', 'INVALID123', '' ] as $not_a_country ) {
			$_SERVER['HTTP_CF_IPCOUNTRY'] = $not_a_country;
			$this->assertNull( Request::get_country(), "$not_a_country was taken as a country." );
		}

		$this->assertSame( 'GB', Request::get_country( [ 'country' => 'GB' ] ), 'A country the caller knows wins.' );
	}

	/**
	 * Cookie and header values keep their percent-encoded octets.
	 *
	 * sanitize_text_field() deletes every %xx sequence, so a URL-encoded
	 * cookie -- which is most of them -- never equalled what the admin typed.
	 */
	public function test_encoded_values_survive(): void {
		$_COOKIE['tracking'] = 'abc%7Cdef%20x';
		$_SERVER['HTTP_X_PROMO'] = '100%25off';

		$this->assertSame( 'abc%7Cdef%20x', Request::get_cookie_value( [], 'tracking:whatever' ) );
		$this->assertSame( '100%25off', Request::get_header_value( [], 'X-Promo:whatever' ) );

		unset( $_COOKIE['tracking'] );
	}

	/**
	 * An Android WebView is a current Chrome, not an old Safari.
	 */
	public function test_a_webview_is_not_an_outdated_browser(): void {
		$webview = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/124.0.0.0 Mobile Safari/537.36';
		$old     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15';

		$this->assertFalse( Request::is_user_agent_outdated_browser( [ 'user_agent' => $webview ] ) );
		$this->assertTrue( Request::is_user_agent_outdated_browser( [ 'user_agent' => $old ] ) );
		$this->assertFalse( Request::is_user_agent_headless( [ 'user_agent' => 'Slack/4.36 Electron/28.1' ] ), 'A desktop app is not a headless browser.' );
	}
}
