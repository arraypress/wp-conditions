<?php
/**
 * MaxMind minFraud Conditions
 *
 * Two conditions are exposed — the raw 0-99 risk score and a
 * convenience "high risk" boolean (≥75, MaxMind's documented
 * threshold). The `arraypress/maxmind-minfraud` Score endpoint is
 * the cheapest of MaxMind's three minFraud tiers and gives us the
 * primary signal a rules engine cares about. Insights / Factors
 * are out of scope for now.
 *
 * Callers MUST pass `minfraud_account_id`, `minfraud_license_key`,
 * and `ip` in `$args`. Email and billing fields, when present,
 * yield higher-quality scoring.
 *
 * @package     ArrayPress\Conditions\Conditions\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Services;

use ArrayPress\Conditions\Clients\MaxMind as MaxMindHelper;

/**
 * Class MaxMind
 *
 * MaxMind minFraud condition catalogue.
 */
class MaxMind {

	/**
	 * Get all MaxMind minFraud conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'MaxMind minFraud', 'arraypress' );

		return [
			'minfraud_risk_score'  => [
				'label'         => __( 'Risk Score', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 75', 'arraypress' ),
				'min'           => 0,
				'max'           => 99,
				'step'          => 1,
				'description'   => __( 'MaxMind minFraud risk score (0-99). Documented thresholds: ≥0.5 elevated, ≥75 high risk, ≥85 very high. Score weighs IP reputation, email/billing checks, and proxy/VPN signals into one number. Each request costs against your minFraud budget — keep an eye on the funds-remaining response field.', 'arraypress' ),
				'compare_value' => fn( $args ) => MaxMindHelper::get_risk_score( $args ),
				'required_args' => [ 'ip', 'minfraud_account_id', 'minfraud_license_key' ],
			],
			'minfraud_is_high_risk' => [
				'label'         => __( 'High Risk', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when minFraud risk score is 75 or above. Equivalent to writing "Risk Score >= 75" but easier to read in compound rules.', 'arraypress' ),
				'compare_value' => fn( $args ) => MaxMindHelper::is_high_risk( $args ),
				'required_args' => [ 'ip', 'minfraud_account_id', 'minfraud_license_key' ],
			],
		];
	}

}
