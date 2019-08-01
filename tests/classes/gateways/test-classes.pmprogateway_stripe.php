<?php

namespace PMPro\Tests\Classes\Gateways;

use \PHPUnit\Framework\TestCase;

/**
 * @testdox Stripe Gateway
 * @covers \PMProGateway_stripe
 */
class PMProGateway_stripe extends TestCase {

	private static $order;

	/**
	 * Set up mock API for running tests.
	 *
	 * @beforeClass
	 */
	public static function setup_mock_stripe_api() {

		echo "Setting up mock Stripe API...\n";

		// Load the Stripe library.
		self::$order = new \MemberOrder();
		self::$order->setGateway( 'stripe' );

		// Set API key and base.
		\Stripe\Stripe::setApiKey( 'sk_test_123' );
		\Stripe\Stripe::$apiBase = 'http://api.stripe.com';

		// set up your tweaked Curl client
		$curl = new \Stripe\HttpClient\CurlClient( [ CURLOPT_PROXY => 'localhost:12111' ] );
		// tell Stripe to use the tweaked client
		\Stripe\ApiRequestor::setHttpClient( $curl );
	}

	/**
	 * Test if the gateway can be initialized.
	 *
	 * @testdox is initialized.
	 */
	function test_is_initialized() {
		$order  = self::$order;
		$result = $order->gateway;
		$this->assertEquals( $result, 'stripe' );
	}

	 /**
	  * Data provider for getCustomer() test.
	  */
	function data_getCustomer() {

		// Order with PaymentMethod
		$order1 = new \MemberOrder();
		$order1->setGateway( 'stripe' );
		$order1->stripeToken = 'tok_12345';
		$order1->Email       = 'test@example.com';

		// Order with Customer ID
		$order2 = new \MemberOrder();
		$order2->setGateway( 'stripe' );
		$order2->Gateway->customer = 'cus_12345';

		return [
			// 'Name of data set' => [
			// $order,
			// $force,
			// $expected
			// ],
			'Order with PaymentMethod - force'     => [
				$order1,
				true,
				'cus_',
			],
			"Order with Customer ID - don't force" => [
				$order2,
				false,
				'cus_12345',
			],
		];
	}

	 /**
	  * Test the getCustomer() method of the PMProGateway_stripe class.
	  *
	  * @testdox can get Customer
	  * @dataProvider data_getCustomer
	  */
	function test_getCustomer( $order, $force, $expected ) {

		$gateway = $order->Gateway;

		// Try to get customer from order.
		$gateway->customer = $gateway->getCustomer( $order, $force );
		$result            = $gateway->customer;

		if ( ! empty( $result->id ) ) {
			// If a Customer was returned, check the ID.
			$this->assertContains( $expected, $result->id );
		} else {
			// If a Customer ID was returned, make sure it's the same.
			$this->assertEquals( $expected, $result );
		}
	}
}
