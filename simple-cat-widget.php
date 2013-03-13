<?php
/*
Plugin Name: Simple Category Widget
Description: An easy to use plugin to show a category in a widget area. You can choose category, number of posts to show and featured image.
Author: Eriks Briedis / Design Schemers
Version: 1.2
Author URI: http://www.designschemers.com
License: GPLv2 or later
*/

/*

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

function ds_widgets_register_widgets() {
		register_widget( 'DsAdvCatWidget' );
	}
add_action( 'widgets_init', 'ds_widgets_register_widgets' );

class DsAdvCatWidget extends WP_Widget {

	function DsAdvCatWidget() {
		$widget_ops = array( 'classname' => 'widget_dsadvcatwidget', 'description' => 'Display posts from a single category' );
		$this->WP_Widget( 'dsadvcat-widget', 'Simple Categories Widget', $widget_ops );
	}


	function widget( $args, $instance ) {
		extract($args);

		echo $before_widget;
		
		if( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title', esc_attr( $instance['title'] ) ) . $after_title;

		$order = 'title' == $instance['order_by'] ? 'ASC' : 'DESC';
		$args = array(
			'showposts' => esc_attr( $instance['num_of_posts'] ),
			'cat'		=> esc_attr( $instance['select'] ),
			'orderby'	=> esc_attr( $instance['order_by'] ),
			'order'		=> $order
		);
		$the_query = new WP_Query( $args );
		if( $the_query->have_posts() ):
			echo '<ul>';
			while ( $the_query->have_posts() ) : $the_query->the_post();
				echo '<li><a href="' . get_permalink() . '">' . substr( get_the_title(), 0, 200 ) . '></a></li>';
				if( $f_image && has_post_thumbnail() )
					echo get_the_post_thumbnail( null, array( esc_attr( $instance['img_width'] ), 9999) );
			endwhile;
			echo '</ul><div style="clear:both"></div>';
		endif;
		wp_reset_postdata();
		
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['select'] = strip_tags($new_instance['select']);
		$instance['order_by'] = strip_tags($new_instance['order_by']);
		$instance['f_image'] = strip_tags($new_instance['f_image']);
		$instance['img_width'] = strip_tags($new_instance['img_width']);
		$instance['num_of_posts'] = strip_tags($new_instance['num_of_posts']);
		return $instance;
	}
	function form( $instance ) {

		// Defaults
		$defaults = array( 'title' => '', 'select' => '', 'order_by' => '', 'f_image' => '', 'img_width' => '', 'num_of_posts' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		// Escape Settings
		$title = esc_attr($instance['title']);
		$select = esc_attr($instance['select']);
		$order_by = esc_attr($instance['order_by']);
		$f_image = esc_attr($instance['f_image']);
		$img_width = esc_attr($instance['img_width']);
		$num_of_posts = esc_attr($instance['num_of_posts']);
		?>

		<p>
	      	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
	      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p>

		<p>
			<label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Please select a category to display'); ?></label>
			<select name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>">
				<?php
				$options = get_categories();
				foreach ($options as $option) {
					echo '<option value="' . $option->cat_ID . '" id="' . $option->category_nicename . '"', $select == $option->cat_ID ? ' selected="selected"' : '', '>', $option->cat_name, '</option>';
				}
				?>
			</select><br />
			<label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Order By: '); ?></label><br />
			<select name="<?php echo $this->get_field_name('order_by'); ?>" id="<?php echo $this->get_field_id('order_by'); ?>">
				<?php $args = array(
					'none'=>1,
					'title'=>2,
					'date'=>3,
					'modified'=>4
				);
				foreach ($args as $item => $value) {
					echo '<option value="' . $item . '" id="' . $item . "-" . $value . '"', $order_by == $item ? ' selected="selected"' : '', '>', ucfirst($item), '</option>';
				}
				?>

			</select>
		</p>
		<p>
	        <input id="<?php echo $this->get_field_id('f_image'); ?>" name="<?php echo $this->get_field_name('f_image'); ?>" onclick="img_width_t()" type="checkbox" value="1" <?php checked( '1', $f_image ); ?>/>
	        <label for="<?php echo $this->get_field_id('f_image'); ?>"><?php _e('Featured image'); ?></label>
	    </p>
	    <p>
	      	<label for="<?php echo $this->get_field_id('img_width'); ?>"><?php _e('Maximum Width'); ?></label>
	      	<input id="<?php echo $this->get_field_id('img_width'); ?>" name="<?php echo $this->get_field_name('img_width'); ?>" size="2" type="text" value="<?php echo $img_width; ?>" />

	    </p>
	    <p>
	      	<label for="<?php echo $this->get_field_id('num_of_posts'); ?>"><?php _e('Number Of Posts'); ?></label>
	      	<input id="<?php echo $this->get_field_id('num_of_posts'); ?>" name="<?php echo $this->get_field_name('num_of_posts'); ?>" type="text" size="2" value="<?php echo $num_of_posts; ?>" />
	    </p>

	    <script>
	    	function init() {
				img_width_t();
			}
			window.onload = init;
			function img_width_t(){
				if(document.getElementById("<?php echo $this->get_field_id('f_image'); ?>").checked == true){
					document.getElementById("<?php echo $this->get_field_id('img_width'); ?>").disabled = false;
				}else if(document.getElementById("<?php echo $this->get_field_id('f_image'); ?>").checked == false){
					document.getElementById("<?php echo $this->get_field_id('img_width'); ?>").disabled = true;
				}
			}
	    </script>
	    <style>
	    	input[type="text"]:disabled{
				background: #DDD;
			}
	    </style>

	<?php
	}
}