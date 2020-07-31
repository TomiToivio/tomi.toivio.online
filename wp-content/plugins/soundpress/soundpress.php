<?php
/*
Plugin Name: SoundPress Plugin
Plugin URI:  https://emadmessiha.wordpress.com/soundpressplugin
Description: SoundCloud plugin for WordPress
Version:     3.0.1
Author:      Emad Messiha
Author URI:  https://emadmessiha.wordpress.com/soundpressplugin
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 
Domain Path: /languages
*/

// For security reasons, this line attempts to block any direct traffic to the plugin
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function soundpress_form(){
    ?>
    <style type="text/css">
        /* Soundpress Modal (background) */
        .soundpressmodal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        
        /* Soundpress Modal Content/Box */
        .soundpressmodal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            max-width: 450px;
            width: 70%; /* Could be more or less, depending on screen size */
        }
        
        /* The Close Button */
        .soundpressmodal-close {
            color: #aaa;
            float: right;
            font-size: 15px;
            font-weight: bold;
        }
        
        .soundpressmodal-close:hover,
        .soundpressmodal-close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <p>
        <label for="soundcloud_url_txt">SoundCloud URL</label><br />
        <input id="soundcloud_url_txt" name="soundcloud_url_txt" style="width:100%" type="text" value="" />
    </p>
        
    <p>
        <label for="sc_height_txt">Height (ex. 80, 450 etc.)</label><br />
        <input id="sc_height_txt" name="sc_height_txt" type="text" style="width:100%" value="auto" />
    </p>
        
    <p>
        <label for="sc_autoplay_txt">Autoplay</label>
        <input id="sc_autoplay_txt" name="sc_autoplay_txt" type="checkbox" />
    </p>
    
    <p>
        <label for="sc_showusername_txt">Show Username</label>
        <input id="sc_showusername_txt" name="sc_showusername_txt" type="checkbox" checked/>
    </p>
    
    <p>
        <label for="sc_showart_txt">Show art</label>
        <input id="sc_showart_txt" name="sc_showart_txt" type="checkbox" checked/>
    </p>
    <?php
}

function add_soundpress_button(){
    ?>
    <div id="soundpress-form" class="soundpressmodal" >
        <div class="soundpressmodal-content">
            <span class="soundpressmodal-close">Cancel</span>
            <?php soundpress_form(); ?>
            <a href="#" id="finish-soundpress" class="button">  <img src="../wp-content/plugins/soundpress/soundpress-tiny.png" /> Done  </a>
        </div>
    </div>
    <a href="#" id="insert-soundpress" class="button"><img src="../wp-content/plugins/soundpress/soundpress-tiny.png" />Add SoundCloud</a>
    <?php
}

function include_soundpress_js_file() {
    wp_enqueue_script('soundpress_button', '/wp-content/plugins/soundpress/soundpress.js', array('jquery'), '1.0', true);
}

add_action('wp_enqueue_media', 'include_soundpress_js_file');
add_action('media_buttons', 'add_soundpress_button', 15);

class wp_soundpress_plugin extends WP_Widget {

	// constructor
	function wp_soundpress_plugin() {
		parent::__construct(false, $name = __('SoundPress Widget', 'wp_soundpress_plugin') );
    }

	// widget form creation
	function form($instance) {	
    	// Check values
        if( $instance) {
            $title = esc_attr($instance['title']);
            $soundcloud_url = esc_attr($instance['soundcloud_url']);
            $sc_height = esc_attr($instance['sc_height']);
            $sc_autoplay = esc_attr($instance['sc_autoplay']);
            $sc_showcomments = esc_attr($instance['sc_showcomments']);
            $sc_reposts = esc_attr($instance['sc_reposts']);
            $sc_hide_related = esc_attr($instance['sc_hide_related']);
            $sc_showusername = esc_attr($instance['sc_showusername']);
            $sc_showart = esc_attr($instance['sc_showart']);
        } else {
            // Setting defaults
            $title = '';
            $soundcloud_url = '';
            $sc_height = 'auto';
            $sc_autoplay = '0';
            $sc_showcomments = '0';
            $sc_reposts = '1';
            $sc_hide_related = '1';
            $sc_showusername = '1';
            $sc_showart = '1';
        }
        
        ?>
        
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_soundpress_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('soundcloud_url'); ?>"><?php _e('SoundCloud URL', 'wp_soundpress_plugin'); ?></label>
        <input class="widefat" placeholder="http://example.com" id="<?php echo $this->get_field_id('soundcloud_url'); ?>" name="<?php echo $this->get_field_name('soundcloud_url'); ?>" type="text" value="<?php echo $soundcloud_url; ?>" />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('sc_height'); ?>"><?php _e('Height (ex. 80, 450 etc.):', 'wp_soundpress_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('sc_height'); ?>" name="<?php echo $this->get_field_name('sc_height'); ?>" type="text" value="<?php echo $sc_height; ?>" />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('sc_autoplay'); ?>"><?php _e('Autoplay:', 'wp_soundpress_plugin'); ?></label>
        <input id="<?php echo esc_attr( $this->get_field_id( 'sc_autoplay' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sc_autoplay' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $sc_autoplay ); ?> />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('sc_showusername'); ?>"><?php _e('Show Username:', 'wp_soundpress_plugin'); ?></label>
        <input id="<?php echo esc_attr( $this->get_field_id( 'sc_showusername' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sc_showusername' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $sc_showusername ); ?> />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('sc_showart'); ?>"><?php _e('Show art:', 'wp_soundpress_plugin'); ?></label>
        <input id="<?php echo esc_attr( $this->get_field_id( 'sc_showart' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sc_showart' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $sc_showart ); ?> />
        </p>
        <?php
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['soundcloud_url'] = strip_tags($new_instance['soundcloud_url']);
        $instance['sc_height'] = strip_tags($new_instance['sc_height']);
        $instance['sc_autoplay'] = strip_tags($new_instance['sc_autoplay']);
        $instance['sc_showcomments'] = strip_tags($new_instance['sc_showcomments']);
        $instance['sc_reposts'] = strip_tags($new_instance['sc_reposts']);
        $instance['sc_hide_related'] = strip_tags($new_instance['sc_hide_related']);
        $instance['sc_showusername'] = strip_tags($new_instance['sc_showusername']);
        $instance['sc_showart'] = strip_tags($new_instance['sc_showart']);
        return $instance;
	}

	// widget display
	function widget($args, $instance) {
        extract( $args );
        // these are the widget options
        
        $title = apply_filters('widget_title', $instance['title']);
        $soundcloud_url = $instance['soundcloud_url'];
        $sc_height = $instance['sc_height'];
        $sc_autoplay = $instance['sc_autoplay'];
        $sc_showcomments = $instance['sc_showcomments'];
        $sc_reposts = $instance['sc_reposts'];
        $sc_hide_related = $instance['sc_hide_related'];
        $sc_showusername = $instance['sc_showusername'];
        $sc_showart = $instance['sc_showart'];
        
        echo '<div class="widget-text wp_widget_plugin_box">';
        // Check if title is set
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        echo $before_widget;
        // Display the widget
        $soundcloud_iframe= '<span>No audio track to show.</span>';
        // Check if soundcloud URL is set & build full url
        $urlarr = array();

        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$soundcloud_url)) {
            $soundcloud_iframe.="<small> (Invalid input URL)</small>";
        } else if ($sc_height != 'auto' && !preg_match('/^(\d*)?\d+$/', $sc_height)) {
            $soundcloud_iframe.="<small> (Invalid input height)</small>";
        } else if ( $soundcloud_url ) {
            // set height
            if ( $sc_height ) {
                $soundcloud_iframe= '<iframe width="100%" scrolling="no" height="'.$sc_height.'" frameborder="no" src="https://w.soundcloud.com/player/?url='.$soundcloud_url;
            }else{
                $soundcloud_iframe= '<iframe width="100%" scrolling="no" height="auto" frameborder="no" src="https://w.soundcloud.com/player/?url='.$soundcloud_url;
            }
        
            // player parameters
            if ( $sc_autoplay ) {
                if($sc_autoplay == '0'){
                    $soundcloud_iframe .= '&amp;auto_play=false';
                }else{
                    $soundcloud_iframe .= '&amp;auto_play=true';
                }
            }
            if ( $sc_showcomments ) {
                if($show_comments == '0'){
                    $soundcloud_iframe .= '&amp;show_comments=false';
                }else{
                    $soundcloud_iframe .= '&amp;show_comments=true';
                }
            }
            
            if ( $sc_showusername ) {
                if($sc_showusername == '0'){
                    $soundcloud_iframe .= '&amp;show_user=false';
                }else{
                    $soundcloud_iframe .= '&amp;show_user=true';
                }
            }
            
            if ( $sc_reposts ) {
                if($sc_reposts == '0'){
                    $soundcloud_iframe .= '&amp;show_reposts=false';
                }else{
                    $soundcloud_iframe .= '&amp;show_reposts=true';
                }
            }
            if ( $sc_hide_related ) {
                if($sc_hide_related == '0'){
                    $soundcloud_iframe .= '&amp;hide_related=false';
                }else{
                    $soundcloud_iframe .= '&amp;hide_related=true';
                }
            }
            
            if ( $sc_showart ) {
                if($sc_showart == '0'){
                    $soundcloud_iframe .= '&amp;visual=false';
                }else{
                    $soundcloud_iframe .= '&amp;visual=true';
                }
            }
            
            $soundcloud_iframe .='"></iframe>';
        }
        
        echo $soundcloud_iframe;
        echo '<br />';
        
        echo $after_widget;
        echo '</div>';
	}
}

// register widget
add_action( 'widgets_init', function() { return register_widget("wp_soundpress_plugin"); }, 1 );
?>