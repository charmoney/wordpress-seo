<?php
/**
 * Date archive watcher to save the meta data to an Indexable.
 *
 * @package Yoast\YoastSEO\Watchers
 */

namespace Yoast\WP\Free\Integrations\Watchers;

use Yoast\WP\Free\Builders\Indexable_Date_Archive_Builder;
use Yoast\WP\Free\Conditionals\Indexables_Feature_Flag_Conditional;
use Yoast\WP\Free\Integrations\Integration_Interface;
use Yoast\WP\Free\Repositories\Indexable_Repository;

/**
 * Watches the date archive options to save the meta information when updated.
 */
class Indexable_Date_Archive_Watcher implements Integration_Interface {

	/**
	 * @inheritdoc
	 */
	public static function get_conditionals() {
		return [ Indexables_Feature_Flag_Conditional::class ];
	}

	/**
	 * @var \Yoast\WP\Free\Repositories\Indexable_Repository
	 */
	protected $repository;

	/**
	 * @var \Yoast\WP\Free\Builders\Indexable_Date_Archive_Builder
	 */
	protected $builder;

	/**
	 * Indexable_Author_Watcher constructor.
	 *
	 * @param Indexable_Repository           $repository The repository to use.
	 * @param Indexable_Date_Archive_Builder $builder    The date archive builder to use.
	 */
	public function __construct( Indexable_Repository $repository, Indexable_Date_Archive_Builder $builder ) {
		$this->repository = $repository;
		$this->builder    = $builder;
	}

	/**
	 * @inheritdoc
	 */
	public function register_hooks() {
		add_action( 'update_option_wpseo_titles', [ $this, 'check_option' ], 10, 2 );
	}

	/**
	 * Checks if the date archive indexable needs to be rebuild based on option values.
	 *
	 * @param array $old_value The old value of the option.
	 * @param array $new_value The new value of the option.
	 *
	 * @return void
	 */
	public function check_option( $old_value, $new_value ) {
		$relevant_keys = [ 'title-archive-wpseo', 'breadcrumbs-archiveprefix', 'metadesc-archive-wpseo', 'noindex-archive-wpseo' ];

		foreach ( $relevant_keys as $key ) {
			// If both values aren't set they haven't changed.
			if ( ! isset( $old_value[ $key ] ) && ! isset( $new_value[ $key ] ) ) {
				continue;
			}

			// If the value was set but now isn't, is set but wasn't or is not the same it has changed.
			if ( ! isset( $old_value[ $key ] ) || ! isset( $new_value[ $key ] ) || $old_value[ $key ] !== $new_value[ $key ] ) {
				$this->build_indexable();
				return;
			}
		}
	}

	/**
	 * Saves the date archive.
	 *
	 * @return void
	 */
	public function build_indexable() {
		$indexable = $this->repository->find_for_date_archive( false );
		$indexable = ( $indexable === false ) ? $this->repository->create_for_date_archive() : $this->builder->build( $indexable );
		$indexable->save();
	}
}
