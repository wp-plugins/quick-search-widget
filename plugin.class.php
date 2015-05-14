<?php 
/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class WCP_Quick_Search_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function WCP_Quick_Search_Widget() {
        $widget_ops = array( 'classname' => 'wcp_quick_search', 'description' => 'Filter results as you type' );
        $this->WP_Widget( 'wcp_quick_search', 'Quick Search Widget', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract($instance);
        extract( $args, EXTR_SKIP );

        /**
         * Search widget styles and scripts
         **/
        wp_enqueue_style( 'wcp-quick-search-styles', plugins_url( 'css/style.css', __FILE__ ));
        wp_enqueue_script( 'angular-js', plugins_url( 'js/angular.min.js', __FILE__ ) );
        wp_enqueue_script( 'angular-animate-js', plugins_url( 'js/angular-animate.min.js', __FILE__ ) );
        wp_enqueue_script( 'wcp-search-widget-js', plugins_url( 'js/script.js', __FILE__ ), array('jquery'));

        echo $before_widget;
        echo $before_title;
        if(isset($wcp_qs_title)) { echo $wcp_qs_title; }
        echo $after_title;
        global $post;
        $args = array( 'numberposts' => -1, 'post_type' => array( $wcp_qs_cpt ), 'post_status'      => array( 'publish') );
        $posts = get_posts( $args );
        $allposts = array();
        foreach ($posts as $post) {
            $allposts[] = array(
                        'title' => $post->post_title,
                        'url' => get_permalink( $post->ID ),
                        'thumb' => get_the_post_thumbnail( $post->ID, 'thumbnail' ),
                        'excerpt' => $post->post_excerpt,
                        'date' => $post->post_date,
                        'id' => $post->ID );
        }
        wp_localize_script( 'wcp-search-widget-js', 'searchdata', array( 'posts' => $allposts) );
        ?>

        <div ng-app="wcpSearchApp">
            <div ng-controller="wcpSearchCtrl">
                <input type="text" ng-model="search.$" name="wcp-search" class="wcp-search-widget-field" placeholder="<?php if(isset($wcp_qs_placeholder) && $wcp_qs_placeholder != '') { echo $wcp_qs_placeholder; } else { echo 'Type to Filter...'; } ?>">
                <ul class="wcp-search <?php if(isset($wcp_qs_animate) && $wcp_qs_animate == 'showanimation'){ echo 'wcp-animation'; } ?>">
                    <li class="wcp-search-item post-{{post.id}}" ng-repeat="post in allPosts | filter:search | limitTo:<?php echo $wcp_qs_qty; ?>">
                        
                    <?php if(isset($wcp_qs_thumbs) && $wcp_qs_thumbs == 'showthumbs'){ echo '<span ng-bind-html="printThumb(post.thumb)"></span>'; } ?>
                        <a href="{{post.url}}"><h4>{{post.title}}</h4></a>
                        <p style="clear: both;">
                            <?php if(isset($wcp_qs_excerpt) && $wcp_qs_excerpt == 'showexcerpt'){ echo '{{post.excerpt}}'; } ?>
                            <?php if(isset($wcp_qs_date) && $wcp_qs_date == 'showdate'){ echo '{{post.date}}'; } ?>
                        </p>

                    </li>
                    <li class="wcp-search-item" ng-show="(allPosts | filter:search).length == 0" style="text-align: center;"><?php if(isset($wcp_qs_noresults) && $wcp_qs_noresults != '') { echo $wcp_qs_noresults; } else { echo 'No Results!'; } ?></li>
                </ul>
            </div>
        </div>
        
        <?php
        echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        extract($instance);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_title'); ?>"><?php _e( 'Title', 'wcp-quicksearch' ); ?></label>
            <input  type="text"
                    class="widefat"
                    name="<?php echo $this->get_field_name('wcp_qs_title'); ?>"
                    id="<?php echo $this->get_field_id('wcp_qs_title'); ?>"
                    value="<?php if (isset($wcp_qs_title)) echo esc_attr($wcp_qs_title); ?>"
            />            
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_cpt'); ?>"><?php _e( 'Search For', 'wcp-quicksearch' ); ?></label>
            <select class="widefat select_post"
                    name="<?php echo $this->get_field_name('wcp_qs_cpt'); ?>"
                    id="<?php echo $this->get_field_id('wcp_qs_cpt'); ?>">
                <option value="post" <?php if(isset($wcp_qs_cpt)) { selected( $wcp_qs_cpt, 'post' ); } ?>>Posts</option>
                <option value="page" <?php if(isset($wcp_qs_cpt)) { selected( $wcp_qs_cpt, 'page' ); } ?>>Pages</option>
                <option value="product"<?php if(isset($wcp_qs_cpt)) { selected( $wcp_qs_cpt, 'product' ); } ?>>Products</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_qty'); ?>"><?php _e( 'Show Number of Results', 'wcp-quicksearch' ); ?></label>
            <input type="number" class="widefat"
                    name="<?php echo $this->get_field_name('wcp_qs_qty'); ?>"
                    id="<?php echo $this->get_field_id('wcp_qs_qty'); ?>"
                    value="<?php if (isset($wcp_qs_qty)) echo esc_attr($wcp_qs_qty); ?>"
            />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_thumbs'); ?>"><input type="checkbox"
                        name="<?php echo $this->get_field_name('wcp_qs_thumbs'); ?>"
                        id="<?php echo $this->get_field_id('wcp_qs_thumbs'); ?>"
                        value="showthumbs" <?php if(isset($wcp_qs_thumbs)) { checked( $wcp_qs_thumbs, 'showthumbs' ); } ?>
                /><?php _e( 'Show Thumbnails', 'wcp-quicksearch' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_excerpt'); ?>"><input type="checkbox"
                        name="<?php echo $this->get_field_name('wcp_qs_excerpt'); ?>"
                        id="<?php echo $this->get_field_id('wcp_qs_excerpt'); ?>"
                        value="showexcerpt" <?php if(isset($wcp_qs_excerpt)) { checked( $wcp_qs_excerpt, 'showexcerpt' ); } ?>
                /><?php _e( 'Show Excerpt', 'wcp-quicksearch' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_date'); ?>"><input type="checkbox"
                        name="<?php echo $this->get_field_name('wcp_qs_date'); ?>"
                        id="<?php echo $this->get_field_id('wcp_qs_date'); ?>"
                        value="showdate" <?php if(isset($wcp_qs_date)) { checked( $wcp_qs_date, 'showdate' ); } ?>
                /><?php _e( 'Show Date', 'wcp-quicksearch' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_animate'); ?>"><input type="checkbox"
                        name="<?php echo $this->get_field_name('wcp_qs_animate'); ?>"
                        id="<?php echo $this->get_field_id('wcp_qs_animate'); ?>"
                        value="showanimation" <?php if(isset($wcp_qs_animate)) { checked( $wcp_qs_animate, 'showanimation' ); } ?>
                /><?php _e( 'Animate Search Results', 'wcp-quicksearch' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_placeholder'); ?>"><?php _e( 'Search Placeholder', 'wcp-quicksearch' ); ?></label>
            <input  type="text"
                    class="widefat"
                    name="<?php echo $this->get_field_name('wcp_qs_placeholder'); ?>"
                    id="<?php echo $this->get_field_id('wcp_qs_placeholder'); ?>"
                    value="<?php if (isset($wcp_qs_placeholder)) echo esc_attr($wcp_qs_placeholder); ?>"
            />        
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wcp_qs_noresults'); ?>"><?php _e( 'No Results Text', 'wcp-quicksearch' ); ?></label>
            <input  type="text"
                    class="widefat"
                    name="<?php echo $this->get_field_name('wcp_qs_noresults'); ?>"
                    id="<?php echo $this->get_field_id('wcp_qs_noresults'); ?>"
                    value="<?php if (isset($wcp_qs_noresults)) echo esc_attr($wcp_qs_noresults); ?>"
            />            
        </p>
        <?php
    }
}

add_action( 'widgets_init', create_function( '', "register_widget( 'WCP_Quick_Search_Widget' );" ) );
add_action( 'admin_enqueue_scripts', 'search_widget_admin_scripts' );

/*
*   Script for Media uploader
 */
function search_widget_admin_scripts($hook){
    if ( 'widgets.php' != $hook ) {
        return;
    }
    wp_enqueue_script( 'wcp-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery') );
}
?>