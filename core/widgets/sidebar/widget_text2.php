<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
class MGM_Text_Widget2 extends WP_Widget {
    /**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'mgm_text_widget2', // Base ID
			'Magic Members Text Widget2', // Name
			array( 'description' => __( 'Magic Members Text Widget2', 'mgm' ), ) // Args
		);
	}

    /**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;
		extract( $args );

		$title = apply_filters( 'mgm_sidebar_widget_text_title', $instance['title'] );
		$text  = apply_filters( 'mgm_sidebar_widget_text_text', $instance['text']);
		

		echo '<!--TEXT WIDGET-->';

		echo $before_widget;
		
		if ( ! empty( $title ) ) :
			echo $before_title . $title . $after_title;
		endif;
	
		if( ! empty($text) ) :
		
			echo '<div class="textwidget">' . $text . '</div>';

		endif;
				
		echo $after_widget;
		
		echo '<!--END TEXT WIDGET-->';
	}

          
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text']  = strip_tags( $new_instance['text'] );
		return $instance;
	}
	
    /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'New title', 'mgm' );
		$text  = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : __( 'New Text', 'mgm' ) ;		
	?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Text:' ); ?></label> 
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" rows="16" cols="20"><?php echo esc_attr( $text ); ?></textarea>
		</p> 

       <?php
     }
}// end of class



