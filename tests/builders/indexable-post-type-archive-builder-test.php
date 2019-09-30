<?php

namespace Yoast\WP\Free\Tests\Builders;

use Brain\Monkey;
use Mockery;
use Yoast\WP\Free\Builders\Indexable_Post_Type_Archive_Builder;
use Yoast\WP\Free\Helpers\Options_Helper;
use Yoast\WP\Free\Helpers\Url_Helper;
use Yoast\WP\Free\Models\Indexable;
use Yoast\WP\Free\ORM\ORMWrapper;
use Yoast\WP\Free\Tests\TestCase;

/**
 * Class Indexable_Author_Test.
 *
 * @group indexables
 * @group builders
 *
 * @coversDefaultClass \Yoast\WP\Free\Builders\Indexable_Author_Builder
 * @covers ::<!public>
 *
 * @package Yoast\Tests\Builders
 */
class Indexable_Post_Type_Archive_Builder_Test extends TestCase {

	/**
	 * Tests the formatting of the indexable data.
	 *
	 * @covers ::build
	 */
	public function test_build() {
		$options_helper_mock = Mockery::mock( Options_Helper::class );
		$options_helper_mock->expects( 'get' )->with( 'title-ptarchive-my-post-type' )->andReturn( 'my_post_type_title' );
		$options_helper_mock->expects( 'get' )->with( 'metadesc-ptarchive-my-post-type' )->andReturn( 'my_post_type_meta_description' );
		$options_helper_mock->expects( 'get' )->with( 'bctitle-ptarchive-my-post-type' )->andReturn( 'my_post_type_breadcrumb_title' );
		$options_helper_mock->expects( 'get' )->with( 'noindex-ptarchive-my-post-type' )->andReturn( false );
		Monkey\Functions\expect( 'get_post_type_archive_link' )->with( 'my-post-type' )->andReturn( 'https://permalink' );

		$indexable_mock      = Mockery::mock( Indexable::class );
		$indexable_mock->orm = Mockery::mock( ORMWrapper::class );
		$indexable_mock->orm->expects( 'set' )->with( 'object_type', 'post-type-archive' );
		$indexable_mock->orm->expects( 'set' )->with( 'object_sub_type', 'my-post-type' );
		$indexable_mock->orm->expects( 'set' )->with( 'title', 'my_post_type_title' );
		$indexable_mock->orm->expects( 'set' )->with( 'breadcrumb_title', 'my_post_type_breadcrumb_title' );
		$indexable_mock->orm->expects( 'set' )->with( 'permalink', 'https://permalink' );
		$indexable_mock->orm->expects( 'get' )->with( 'permalink' )->andReturn( 'https://permalink' );
		$indexable_mock->orm->expects( 'set' )->with( 'canonical', 'https://permalink' );
		$indexable_mock->orm->expects( 'set' )->with( 'description', 'my_post_type_meta_description' );
		$indexable_mock->orm->expects( 'set' )->with( 'is_robots_noindex', false );

		$builder = new Indexable_Post_Type_Archive_Builder( $options_helper_mock );
		$builder->build( 'my-post-type', $indexable_mock );
	}
}
