<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Admin_Custom_Dimensions {
	/**
	 * @var array Contains all the custom dimensions by type, with parameters 'title', 'active' and 'enabled'.
	 */
	public $custom_dimensions;

	/**
	 * @var int The amount of custom dimensions currently active and enabled (used in the custom dimensions view).
	 */
	public $custom_dimensions_usage;

	/**
	 * @var int The maximum amount of custom dimensions that could be active (used in the custom dimensions view).
	 */
	public $custom_dimensions_limit;

	/**
	 * @var array
	 */
	public $active_custom_dimensions_types;

	/**
	 * @var array Contains the active custom dimensions as they were saved to the database.
	 */
	public $active_custom_dimensions = array();

	/**
	 * @var array The seo dimension types
	 */
	public $seo_dimension_types = array( 'focus_keyword', 'seo_score' );

	public function __construct() {
		$this->set_active_custom_dimensions();
		$this->set_rendering_properties();
	}

	/**
	 * Fetches the active custom dimensions and assigns them to the active_custom_dimensions property
	 */
	public function set_active_custom_dimensions() {
		$this->active_custom_dimensions = exactmetrics_get_option( 'custom_dimensions', array() );
	}

	/**
	 * The current supported custom dimensions types (Key name is the matching name for the functions). The metric
	 * is a setting for this specific custom dimension. The metric is used to fetch data with this custom dimension.
	 *
	 * @return array
	 */
	public function custom_dimensions() {
		return apply_filters( 'exactmetrics_available_custom_dimensions',
			array(
				'logged_in'              => array(
					'title'   => __( 'Logged in', 'exactmetrics-dimensions' ),
					'label'   => __( 'Number of logged-in sessions', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'This shows sessions from users who are logged into your site. Compare how many pages logged-in visitors view against those not signed in. You can use this data to customize content for registered users to increase their engagement and loyalty.', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'sessions',
				),
				'user_id'                => array(
					'title'   => __( 'User ID', 'exactmetrics-dimensions' ),
					'label'   => __( 'Top logged-in users by sessions', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Top logged-in users by sessions', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'sessions',
				),
				'post_type'              => array(
					'title'   => __( 'Post type', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular post types', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'This shows which types of posts attract the most views on your site. You can use this information to focus on creating content that resonates with your audience, optimizing your efforts toward what truly engages them.', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'author'                 => array(
					'title'   => __( 'Author', 'exactmetrics-dimensions' ),
					'label'   => __( "Most popular authors", 'exactmetrics-dimensions' ),
					'tooltip'   => __( "This shows which authors' articles receive the most views on your site. You can use this to help you identify top-performing writers.", 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'category'               => array(
					'title'   => __( 'Category', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular categories', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Reveals the topics that attract the most interest from your audience. You can use this data to create content on subjects that engage and resonate with your visitors.', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'published_at'           => array(
					'title'   => __( 'Published at', 'exactmetrics-dimensions' ),
					'label'   => __( "Best publication time", 'exactmetrics-dimensions' ),
					'tooltip'   => __( "Determines when your content receives the most views and interaction. You can use this insight to schedule your posts when they're likely to achieve the highest engagement.", 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'modified_at'           => array(
					'title'   => __( 'Modified at', 'exactmetrics-dimensions' ),
					'label'   => __( 'Best modification time', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'publish_day_of_week'   => array(
					'title'   => __( 'Publish day of the week', 'exactmetrics-dimensions' ),
					'label'   => __( "Best Publish day of the week", 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'link_type'    => array(
					'title'   => __( 'Link Type', 'exactmetrics-dimensions' ),
					'label'   => __( 'Best Link Types', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'tags'                   => array(
					'title'   => __( 'Tags', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular tags', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Identifies the tags associated with your most viewed content. You can use this information to tag future posts effectively, aligning them with topics that interest your audience and increase content visibility.', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'seo_score'              => array(
					'title'   => __( 'SEO Score', 'exactmetrics-dimensions' ),
					'label'   => __( 'Best SEO score', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Best SEO score', 'exactmetrics-dimensions' ),
					'enabled' => exactmetrics_is_wp_seo_active(),
					'metric'  => 'pageviews',
				),
				'focus_keyword'          => array(
					'title'   => __( 'Focus Keyword', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular focus keywords', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Most popular focus keywords', 'exactmetrics-dimensions' ),
					'enabled' => exactmetrics_is_wp_seo_active(),
					'metric'  => 'pageviews',
				),
				'aioseo_truseo_score'    => array(
					'title'   => __( 'TruSeo Score', 'exactmetrics-dimensions' ),
					'label'   => __( "TruSEO Score (AIOSEO)", 'exactmetrics-dimensions' ),
					'tooltip'   => __( "TruSEO Score, provided by All in One SEO, rates your content's on-page SEO on a scale of 100. It reflects how well your page or post is optimized, combining analysis of the page itself, your focus's effectiveness, and additional keyphrases.", 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'exactmetrics_is_aioseo_active' ) && exactmetrics_is_aioseo_active(),
					'metric'  => 'pageviews',
				),
				'aioseo_focus_keyphrase' => array(
					'title'   => __( 'Focus Keyphrase', 'exactmetrics-dimensions' ),
					'label'   => __( 'Focus Keyphrase (AIOSEO)', 'exactmetrics-dimensions' ),
					'tooltip'   => __( 'Track and analyze the keywords targeted by your posts and pages. Use this data to refine your content strategy, improve your search engine rankings, and attract more visitors to your site.', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'exactmetrics_is_aioseo_active' ) && exactmetrics_is_aioseo_active(),
					'metric'  => 'pageviews',
				),
				'rankmath_seo_score'    => array(
					'title'   => __( 'RankMath SEO Score', 'exactmetrics-dimensions' ),
					'label'   => __( "RankMath SEO Score", 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'rank_math' ),
					'metric'  => 'pageviews',
				),
				'rankmath_focus_keyword'    => array(
					'title'   => __( 'RankMath Focus Keyword', 'exactmetrics-dimensions' ),
					'label'   => __( 'RankMath Focus Keyword', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'rank_math' ),
					'metric'  => 'pageviews',
				),
				'seopress_seo_score'    => array(
					'title'   => __( 'SEOPress Score', 'exactmetrics-dimensions' ),
					'label'   => __( 'SEOPress Score', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'seopress_get_service' ),
					'metric'  => 'pageviews',
				),
				'seopress_keywords'    => array(
					'title'   => __( 'SEOPress Target Keywords', 'exactmetrics-dimensions' ),
					'label'   => __( 'SEOPress Target Keywords', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'seopress_get_service' ),
					'metric'  => 'pageviews',
				),
			)
		);
	}

	/**
	 * Checks if the given dimensions all have a unique ID
	 *
	 * @param array $dimensions Dimensions to check.
	 *
	 * @return bool Whether or not the dimension IDs are unique.
	 */
	public function dimension_ids_are_unique( $dimensions ) {
		$dimension_ids = wp_list_pluck( $dimensions, 'id' );

		return $dimension_ids === array_unique( $dimension_ids );
	}

	/**
	 * Checks if the given dimensions all have a unique type
	 *
	 * @param array $dimensions Dimensions to check.
	 *
	 * @return bool Whether or not the dimension types are unique.
	 */
	public function dimension_types_are_unique( $dimensions ) {
		$dimension_ids = wp_list_pluck( $dimensions, 'type' );

		return $dimension_ids === array_unique( $dimension_ids );
	}

	/**
	 * @return bool Checks if there are any active seo dimensions
	 */
	public function seo_dimensions_active() {
		$active_seo_dimension_types = array_intersect( $this->seo_dimension_types, $this->active_custom_dimensions_types() );

		return ! empty( $active_seo_dimension_types );
	}

	/**
	 * Prepares a couple of properties to be used in the custom dimensions view
	 */
	public function set_rendering_properties() {
		$this->custom_dimensions              = $this->custom_dimensions();
		$this->active_custom_dimensions_types = $this->active_custom_dimensions_types();
		$this->custom_dimensions_usage        = count( $this->active_enabled_custom_dimensions() );
		$this->custom_dimensions_limit        = count( $this->enabled_custom_dimensions() );
	}

	/**
	 * Returns an array with custom dimensions that are both active and enabled.
	 *
	 * @return array
	 */
	private function active_enabled_custom_dimensions() {
		$active_enabled_custom_dimensions = array();

		foreach ( $this->enabled_custom_dimensions() as $key => $custom_dimension ) {
			if ( in_array( $key, $this->active_custom_dimensions_types ) ) {
				$active_enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $active_enabled_custom_dimensions;
	}

	/**
	 * Returns an array with all enabled custom dimensions, both active and inactive.
	 *
	 * @return array
	 */
	private function enabled_custom_dimensions() {
		$enabled_custom_dimensions = array();

		foreach ( $this->custom_dimensions as $key => $custom_dimension ) {
			if ( $custom_dimension['enabled'] ) {
				$enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $enabled_custom_dimensions;
	}

	/**
	 * Maps the types of the active custom dimensions to a separate array to be analyzed in $this->custom_dimensions()
	 *
	 * @return array
	 */
	private function active_custom_dimensions_types() {
		$active_custom_dimensions_types = array();

		foreach ( $this->active_custom_dimensions as $active_custom_dimension ) {
			$active_custom_dimensions_types[] = $active_custom_dimension['type'];
		}

		return $active_custom_dimensions_types;
	}
}
