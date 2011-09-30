<?php

class envatoReferralLinkWidget extends WP_Widget
{
	// Instantiate the parent object
	function envatoReferralLinkWidget()
	{	$widget_ops = array(
			'description' => __( 'Add referral link to envato site ', 'wpsc' )
		);

		$this->WP_Widget( false, 'Envato Referral Link', $widget_ops );
	}

	// Output admin widget options form
	function form( $instance )
	{
		// Defaults
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'width' => 45,
			'height' => 45,
			'image' => false,
			'grid' => false,
			'show_name' => false,
		));

		// Values
		$title    = esc_attr( $instance['title'] );
		$image    = (bool) $instance['image'];
		$width    = (int) $instance['width'];
		$height   = (int) $instance['height'];
		$grid     = (bool) $instance['grid'];
		$show_name= (bool) $instance['show_name'];	

		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'wpsc' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php

	}

	// Save widget options
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	// Widget output
	function widget( $args, $instance )
	{
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Envato Sites:', 'wpsc'  ) : $instance['title'] );

		$output = '';
		$output .=  $before_widget;
		$output .=  '<div class="envato_referral_link_widget">'."\n";

		if ( $title )
			$output .=  $before_title . $title . $after_title;

		$output .= '<br />'."\n";
		$output .= do_shortcode('[envato_referral_link]');
		$output .=  '</div>'."\n";
		$output .=  $after_widget;

		echo $output;
		return $output;

	}

}

function envato_referral_link_register_widget()
{
	register_widget( 'envatoReferralLinkWidget' );
}

add_action( 'widgets_init', 'envato_referral_link_register_widget' );

?>