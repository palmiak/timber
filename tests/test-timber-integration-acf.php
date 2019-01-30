<?php

use Timber\Integrations\ACF;

class TestTimberIntegrationACF extends Timber_UnitTestCase {
	function testACFInit() {
		$acf = new ACF();
		$this->assertInstanceOf( 'Timber\Integrations\ACF', $acf );
	}

	function testACFGetFieldPost() {
		$pid = $this->factory->post->create();
		update_field( 'subhead', 'foobar', $pid );
		$str = '{{post.meta("subhead")}}';
		$post = new Timber\Post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	function testACFHasFieldPostFalse() {
		$pid = $this->factory->post->create();
		$str = '{% if post.has_field("heythisdoesntexist") %}FAILED{% else %}WORKS{% endif %}';
		$post = new Timber\Post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('WORKS', $str);
	}

	function testACFHasFieldPostTrue() {
		$pid = $this->factory->post->create();
		update_post_meta($pid, 'best_radiohead_album', 'in_rainbows');
		$str = '{% if post.has_field("best_radiohead_album") %}In Rainbows{% else %}OK Computer{% endif %}';
		$post = new Timber\Post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('In Rainbows', $str);
	}

	function testACFGetFieldTermCategory() {
		update_field( 'color', 'blue', 'category_1' );
		$cat = new Timber\Term( 1 );
		$this->assertEquals( 'blue', $cat->color );
		$str = '{{term.color}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $cat ) ) );
	}

	function testACFCustomFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'green', 'post_tag_'.$tid );
		$term = new Timber\Term( $tid );
		$str = '{{term.color}}';
		$this->assertEquals( 'green', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFGetFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'blue', 'post_tag_'.$tid );
		$term = new Timber\Term( $tid );
		$str = '{{term.meta("color")}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFFieldObject() {
		$key = 'field_5ba2c660ed26d';
		$fp_id = $this->factory->post->create(array('post_content' => 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}', 'post_title' => 'Thinger', 'post_name' => $key, 'post_type' => 'acf-field'));
		$pid      = $this->factory->post->create();
		update_field( 'thinger', 'foo', $pid );
		update_field( '_thinger', $key, $pid );
		$post     = new Timber\Post($pid);
		$template = '{{ post.meta("thinger") }} / {{ post.field_object("thinger").key }}';
		$str      = Timber::compile_string($template, array( 'post' => $post ));
		$this->assertEquals('foo / '.$key, $str);
	}

	function testACFFormatValue() {
		acf_add_local_field_group( array(
			'key'      => 'group_1',
			'title'    => 'Group 1',
			'fields'   => [
				[
					'key'   => 'field_1',
					'label' => 'Lead',
					'name'  => 'lead',
					'type'  => 'wysiwyg',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					],
				],
			],
		) );

		$post_id = $this->factory->post->create();
		$post    = new Timber\Post( $post_id );
		update_field( 'lead', 'Murder Spagurders are dangerous sneks.', $post_id );

		$string = trim( Timber::compile_string( "{{ post.meta('lead') }}", [ 'post' => $post ] ) );
		$this->assertEquals( '<p>Murder Spagurders are dangerous sneks.</p>', $string );

		$string = trim( Timber::compile_string( "{{ post.meta('lead', { format_value: false }) }}", [ 'post' => $post ] ) );
		$this->assertEquals( 'Murder Spagurders are dangerous sneks.', $string );
	}

	/**
	 * @expectedDeprecated {{ post.get_field('field_name') }}
	 */
	function testPostGetFieldDeprecated() {
		$post_id = $this->factory->post->create();
		$post    = new Timber\Post( $post_id );

		$post->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ term.get_field('field_name') }}
	 */
	function testTermGetFieldDeprecated() {
		$term_id = $this->factory->term->create();
		$term    = new Timber\Term( $term_id );

		$term->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ user.get_field('field_name') }}
	 */
	function testUserGetFieldDeprecated() {
		$user_id = $this->factory->user->create();
		$user    = new Timber\User( $user_id );

		$user->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ comment.get_field('field_name') }}
	 */
	function testCommentGetFieldDeprecated() {
		$comment_id = $this->factory->comment->create();
		$comment    = new Timber\Comment( $comment_id );

		$comment->get_field( 'field_name' );
	}
}
