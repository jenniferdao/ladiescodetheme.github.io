<?php
/*
 * Plugin Name: Testimonial Flashing Widget
 * Description: This testimonial widget will flash when different testimonials are displayed in the sidebar. 
 * Version: 1.0
 * Authors: Ladies Code
 */

/*
 * In order for the widget to function, widgets_init will allow for the actual widget to load properly.
 */
add_action( 'widgets_init', 'testimonials_widgets' );

/*
 * This is allows the widget to register. 
 */
function testimonials_widgets() {
	register_widget( 'Testimonials_Widget' );
}

/*
 * Below is defined as the widget class, it will be applied to all classes with the same name.
 * The class testimonials_widget will results in any changes that are followed by the exact class name.
 */
class testimonials_widget extends WP_Widget {
	/* ---------------------------- */
	/* -------- This is the widget setup -------- */
	/* ---------------------------- */
	function Testimonials_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'testimonials_widget', 'description' => __('Testimonial widget plugin allows you display testimonials in a sidebar on your WordPress blog.') );

		/* This is the widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'testimonials_widget' );

		/* This is to create the widget. */
		$this->WP_Widget( 'testimonials_widget', __('Testimonials Widget'), $widget_ops, $control_ops );
	}

	/* ---------------------------- */
	/* ------- The following is to display the widget -------- */
	/* ---------------------------- */
	function widget( $args, $instance ) {
		extract( $args );

		/* This is the variables from the widget settings. */
		$title = apply_filters('the_title', $instance['title']);
		$min_height = $instance['min_height'];
		$show_author = $instance['show_author'];
		$show_source = $instance['show_source'];
		$random_order = $instance['random_order'];
		$refresh_interval = $instance['refresh_interval'];
		$char_limit = $instance['char_limit'];
		$tags = $instance['tags'];

		$testimonials = testimonialswidget_display_testimonials($title, $random_order, $min_height, $refresh_interval, $show_source, $show_author, $tags, $char_limit, $this->number);

		/* Before widget (defined by themes). */
			echo $before_widget;

		/* This displays the widget before and after widget is defined by themees. */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* This will display the widget. */
			echo $testimonials;

		/* After widget (defined by themes). */
			echo $after_widget;
		}

	/* ---------------------------- */
	/* ------- This Process Updates The Widget -------- */
	/* ---------------------------- */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['min_height'] = strip_tags(stripslashes($new_instance['min_height']));
		$instance['show_author'] = (isset($new_instance['show_author']) && $new_instance['show_author'])?1:0;
		$instance['show_source'] = (isset($new_instance['show_source']) && $new_instance['show_source'])?1:0;
		$instance['refresh_interval'] = strip_tags(stripslashes($new_instance['refresh_interval']));
		$instance['random_order'] = (isset($new_instance['random_order']) && $new_instance['random_order'])?1:0;
		$instance['tags'] = strip_tags(stripslashes($new_instance['tags']));
		$instance['char_limit'] = strip_tags(stripslashes($new_instance['char_limit']));
		if(!$instance['char_limit'])
			$instance['char_limit'] = __('none', 'testimonials-widget');

		return $instance;
	}

	/* ---------------------------- */
	/* ------- Widget Settings ------- */
	/* ---------------------------- */

	/**
	 * The following will display the widget settings on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 */
	function form( $instance ) {
		/* Default widget settings. */
		$defaults = array(
			'title' => __('Testimonials', 'testimonials-widget'),
			'min_height' => 150,
			'show_author' => 1,
			'show_source' => 1,
			'random_order' => 1,
			'refresh_interval' => 10,
			'tags' => '',
			'char_limit' => 500
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		// This displays the widget options menu  
		$show_author_checked = $show_source_checked	= $random_order_checked = '';
        if($instance['show_author'])
        	$show_author_checked = ' checked="checked"';
        if($instance['show_source'])
        	$show_source_checked = ' checked="checked"';
        if($instance['random_order'])
        	$random_order_checked = ' checked="checked"';

		
		
		// This displays the minimum height that the testimonials will be displayed in
		echo '<p><label for="'.$this->get_field_id( 'min_height' ).'">'.__('Minimum Height', 'testimonials-widget').' </label><input class="widefat" type="text" id="'.$this->get_field_id( 'min_height' ).'" name="'.$this->get_field_name( 'min_height' ).'" value="'.htmlspecialchars($instance['min_height'], ENT_QUOTES).'" /><br/><span class="setting-description"><small>'.__('Minimum height in px, this must be set to a value that suits your logest testimonial (increase this value if you find that your testimonials are getting cut off).', 'testimonials-widget').'</small></span></p>';
		
		
		// This displays the title
		echo '<p><label for="'.$this->get_field_id( 'title' ).'">'.__('Title', 'testimonials-widget').' </label><input class="widefat" type="text" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" value="'.htmlspecialchars($instance['title'], ENT_QUOTES).'" /></p>';
		
		
		// This shows the source of the testimonial
		echo '<p><input type="checkbox" id="'.$this->get_field_id( 'show_source' ).'" name="'.$this->get_field_name( 'show_source' ).'" value="1"'.$show_source_checked.' /> <label for="'.$this->get_field_id( 'show_source' ).'">'.__('Show source?', 'testimonials-widget').'</label></p>';
		
		
		// This displays the author of the testimonial 
		echo '<p><input type="checkbox" id="'.$this->get_field_id( 'show_author' ).'" name="'.$this->get_field_name( 'show_author' ).'" value="1"'.$show_author_checked.' /> <label for="'.$this->get_field_id( 'show_author' ).'">'.__('Show author?', 'testimonials-widget').'</label></p>';
		
		
		
		// The user can access advanced options
		echo "<p style=\"text-align:left;\"><small><a id=\"".$this->get_field_id( 'adv_key' )."\" style=\"cursor:pointer;\" onclick=\"jQuery('div#".$this->get_field_id( 'adv_opts' )."').slideToggle();\">".__('Advanced options', 'testimonials-widget')." &raquo;</a></small></p>";
		
		
		// This refreshes the speed of the flash function of the widget and how the testimonials update
		echo '<p><label for="'.$this->get_field_id( 'refresh_interval' ).'">'.__('Refresh Interval', 'testimonials-widget').' </label><input class="widefat" type="text" id="'.$this->get_field_id( 'refresh_interval' ).'" name="'.$this->get_field_name( 'refresh_interval' ).'" value="'.htmlspecialchars($instance['refresh_interval'], ENT_QUOTES).'" /><br/><span class="setting-description"><small>'.__('In seconds or 0 for no refresh.', 'testimonials-widget').'</small></span></p>';
		
		// With this option the user can access advance options 
		echo '<div id="'.$this->get_field_id( 'adv_opts' ).'" style="display:none">';
		
		
		// This allows the user to separate the testimonial information with a comma
		echo '<p><label for="'.$this->get_field_id( 'tags' ).'">'.__('Tags filter', 'testimonials-widget').' </label><input class="widefat" type="text" id="'.$this->get_field_id( 'tags' ).'" name="'.$this->get_field_name( 'tags' ).'" value="'.htmlspecialchars($instance['tags'], ENT_QUOTES).'" /><br/><span class="setting-description"><small>'.__('Comma separated', 'testimonials-widget').'</small></span></p>';
		
		//This displays the testimonials in a random order
		echo '<p><input type="checkbox" id="'.$this->get_field_id( 'random_order' ).'" name="'.$this->get_field_name( 'random_order' ).'" value="1"'.$random_order_checked.' /> <label for="'.$this->get_field_id( 'random_order' ).'">'.__('Random order', 'testimonials-widget').'</label><br/><span class="setting-description"><small>'.__('Unchecking this will rotate testimonials in the order added, latest first.', 'testimonials-widget').'</small></span></p>';
		
		//This has a character limit for the amount of words that can be entered into the testimonial
		echo '<p><label for="'.$this->get_field_id( 'char_limit' ).'">'.__('Character limit', 'testimonials-widget').' </label><input class="widefat" type="text" id="'.$this->get_field_id( 'char_limit' ).'" name="'.$this->get_field_name( 'char_limit' ).'" value="'.htmlspecialchars($instance['char_limit'], ENT_QUOTES).'" /></p>';
		echo '</div>';
	}
}
?>
