<?php
/**
 * Presentation object for indexables.
 *
 * @package Yoast\YoastSEO\Presentations
 */

namespace Yoast\WP\Free\Presentations;

use Yoast\WP\Free\Helpers\Post_Type_Helper;
use Yoast\WP\Free\Helpers\Rel_Adjacent_Helper;
use Yoast\WP\Free\Wrappers\WP_Rewrite_Wrapper;

/**
 * Class Indexable_Post_Type_Presentation
 */
class Indexable_Post_Type_Presentation extends Indexable_Presentation {

	/**
	 * @var Post_Type_Helper
	 */
	protected $post_type_helper;

	/**
	 * @var WP_Rewrite_Wrapper
	 */
	protected $wp_rewrite_wrapper;

	/**
	 * @var Rel_Adjacent_Helper
	 */
	protected $rel_adjacent;

	/**
	 * Indexable_Post_Type_Presentation constructor.
	 *
	 * @param Post_Type_Helper    $post_type_helper   The post type helper.
	 * @param WP_Rewrite_Wrapper  $wp_rewrite_wrapper The WP_Rewrite wrapper.
	 * @param Rel_Adjacent_Helper $rel_adjacent       The rel adjacent helper.
	 */
	public function __construct(
		Post_Type_Helper $post_type_helper,
		WP_Rewrite_Wrapper $wp_rewrite_wrapper,
		Rel_Adjacent_Helper $rel_adjacent
	) {
		$this->post_type_helper   = $post_type_helper;
		$this->wp_rewrite_wrapper = $wp_rewrite_wrapper;
		$this->rel_adjacent       = $rel_adjacent;
	}

	/**
	 * @inheritDoc
	 */
	public function generate_canonical() {
		if ( $this->model->canonical ) {
			return $this->model->canonical;
		}

		$canonical = get_permalink( $this->model->object_id );

		// Fix paginated pages canonical, but only if the page is truly paginated.
		$page_number = \get_query_var( 'page' );
		if ( $page_number > 1 ) {
			$number_of_pages = $this->model->number_of_pages;
			if ( $number_of_pages && $page_number <= $number_of_pages ) {
				if ( ! $this->wp_rewrite_wrapper->get()->using_permalinks() ) {

					$canonical = \add_query_arg( 'page', $page_number, $canonical );
				}
				else {
					$canonical = \user_trailingslashit( \trailingslashit( $canonical ) . $page_number );
				}
			}
		}

		return $this->canonical_helper->after_generate( $canonical );
	}

	/**
	 * @inheritDoc
	 */
	public function generate_rel_prev() {
		$number_of_pages = (int) $this->model->number_of_pages;

		if ( $number_of_pages === null ) {
			return '';
		}

		if ( $this->rel_adjacent->is_disabled() ) {
			return '';
		}

		$current_page = \max( 1, (int) \get_query_var( 'page' ) );

		if ( $current_page < 2  ) {
			return '';
		}

		$url = \get_permalink( $this->model->object_id );

		$prev_url = $this->rel_adjacent->get_paginated_url( $url, $current_page - 1 );

		if ( ! $prev_url ) {
			return '';
		}

		return $prev_url;
	}

	/**
	 * @inheritDoc
	 */
	public function generate_rel_next() {
		if ( $this->model->number_of_pages === null ) {
			return '';
		}

		if ( $this->rel_adjacent->is_disabled() ) {
			return '';
		}

		$current_page = \max( 1, (int) \get_query_var( 'page' ) );

		if ( $current_page < 1 || $this->model->number_of_pages <= $current_page ) {
			return '';
		}

		$url = \get_permalink( $this->model->object_id );

		$next_url = $this->rel_adjacent->get_paginated_url( $url, $current_page + 1 );

		if ( ! $next_url ) {
			return '';
		}

		return $next_url;
	}

	/**
	 * @inheritDoc
	 */
	public function generate_title() {
		if ( $this->model->title ) {
			return $this->model->title;
		}

		return $this->options_helper->get( 'title-' . $this->model->object_sub_type );
	}

	/**
	 * @inheritDoc
	 */
	public function generate_meta_description() {
		if ( $this->model->description ) {
			return $this->model->description;
		}

		return $this->options_helper->get( 'metadesc-' . $this->model->object_sub_type );
	}

	/**
	 * Generates the open graph images.
	 *
	 * @return array The open graph images.
	 */
	public function generate_og_images() {
		if ( \post_password_required() ) {
			return [];
		}

		return parent::generate_og_images();
	}

	/**
	 * @inheritDoc
	 */
	public function generate_og_type() {
		return 'article';
	}

	/**
	 * @inheritDoc
	 */
	public function generate_replace_vars_object() {
		return \get_post( $this->model->object_id );
	}

	/**
	 * @inheritDoc
	 */
	public function generate_robots() {
		$robots = array_merge(
			$this->robots_helper->get_base_values( $this->model ),
			[
				'noimageindex' => ( $this->model->is_robots_noimageindex === true ) ? 'noimageindex' : null,
				'noarchive'    => ( $this->model->is_robots_noarchive === true ) ? 'noarchive' : null,
				'nosnippet'    => ( $this->model->is_robots_nosnippet === true ) ? 'nosnippet' : null,
			]
		);

		$private           = \get_post_status( $this->model->object_id ) === 'private';
		$post_type_noindex = ! $this->post_type_helper->is_indexable( $this->model->object_sub_type );

		if ( $private || $post_type_noindex ) {
			$robots['index'] = 'noindex';
		}

		return $this->robots_helper->after_generate( $robots );
	}

	/**
	 * @inheritDoc
	 */
	public function generate_twitter_description() {
		$twitter_description = parent::generate_twitter_description();

		if ( $twitter_description ) {
			return $twitter_description;
		}

		$excerpt = \wp_strip_all_tags( \get_the_excerpt( $this->model->object_id ) );
		if ( $excerpt ) {
			return $excerpt;
		}

		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function generate_twitter_image() {
		if ( \post_password_required() ) {
			return '';
		}

		return parent::generate_twitter_image();
	}

	/**
	 * @inheritDoc
	 */
	public function generate_twitter_creator() {
		$twitter_creator = \ltrim( \trim( \get_the_author_meta( 'twitter', $this->context->post->post_author ) ), '@' );

		/**
		 * Filter: 'wpseo_twitter_creator_account' - Allow changing the Twitter account as output in the Twitter card by Yoast SEO.
		 *
		 * @api string $twitter The twitter account name string.
		 */
		$twitter_creator = \apply_filters( 'wpseo_twitter_creator_account', $twitter_creator );

		if ( \is_string( $twitter_creator ) && $twitter_creator !== '' ) {
			return '@' . $twitter_creator;
		}

		$site_twitter = $this->options_helper->get( 'twitter_site', '' );
		if ( \is_string( $site_twitter ) && $site_twitter !== '' ) {
			return '@' . $site_twitter;
		}

		return '';
	}
}
