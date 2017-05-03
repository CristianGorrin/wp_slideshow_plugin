<?php
/*
 * Plugin Name: Slideshow
 * Description: Slideshow plugin
 * Version: 0.0.1
 * Author: Cristian C. Gorrin
 * License: GLPv2
 */
namespace slideshow;
use WP_Query;

function GetNextId() {
    static $id_counter;
    if ($id_counter == null) {
        $id_counter = 0;
    }

    return 'slideshow_plugin_' . $id_counter++;
}
function FormatImgStr($str) {
    return trim(explode('=', $str)[1], '"');
}

//Custom Post Type
function SetCustomPostType() {
    $singular = 'Slide';
    $plural   = 'Slides';

    //https://developer.wordpress.org/plugins/users/roles-and-capabilities/
    $capabilities = array(

    );

    $labels = array(
        'name'               => $plural,
        'singular_name'      => $singular,
        'add_name'           => 'Add new',
        'add_new_item'       => 'Add new ' . $singular,
        'edit'               => 'edit',
        'edit_item'          => 'Edit ' . $singular,
        'new_item'           => 'New ' . $singular,
        'view'               => 'View ' . $singular,
        'view_item'          => 'View ' . $singular,
        'search_term'        => 'Search '. $plural,
        'parent'             => 'Parent ' . $singular,
        'not_found'          => 'No ' . $plural . ' found',
        'not_found_in_trash' => 'No ' . $plural . ' in Trash'
    );

    register_post_type(
        'slide',
        array(
            'labels'              => $labels,
		    'public'              => true,
		    'publicly_queryable'  => true,
		    'exclude_from_search' => false,
		    'show_ui'             => true,
		    'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
		    'has_archive'         => true,
		    'rewrite'             => true,
		    'query_var'           => true,
            'menu_icon'           => 'dashicons-format-gallery', //Find in https://developer.wordpress.org/resource/dashicons
            'menu_position'       => 6,
            'can_export'          => true,
            'delete_with_user'    => false,
            'capability_type'     => 'post',
            'capabilities'        => $capabilities,
            'rewrite'             => array(
                'slug' => 'Slide',
                'with_front' => true,
                'pages'      => true,
                'feeds'      => true
            ),
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'custom-fields'
            )
        )
    );

    register_taxonomy(
        'team_tag',
        'slide',
        array(
            'hierarchical'  => false,
            'label'         => 'Slide Grupes',
            'singular_name' => 'Slide Grupe',
            'rewrite'       => true,
            'query_var'     => true
        )
    );
}
add_action('init', '\\slideshow\\SetCustomPostType');

//Scripts
function AddScripts() {
    wp_enqueue_script('slide_init', plugin_dir_url(__FILE__) . 'js/init.js', array(), '1', false);
    wp_enqueue_script('slide_jquery', plugin_dir_url(__FILE__) . 'js/jquery.min.js', array(), '1', true);
    wp_enqueue_script('slide_slides', plugin_dir_url(__FILE__) . 'js/jquery.slides.min.js', array('slide_jquery'), '1', true);
    wp_enqueue_script('slide_setup',  plugin_dir_url(__FILE__) . 'js/slides_setup.js', array('slide_slides', 'slide_init'), '1', true);
    wp_enqueue_style('slide_font_awesome', plugin_dir_url(__FILE__) . 'css/font-awesome.min.css');
    wp_enqueue_style('slide_main', plugin_dir_url(__FILE__) . 'css/slides.css');
}
add_action('wp_enqueue_scripts', '\\slideshow\\AddScripts');

//Shortcode
function Shortcode($atts, $content = null) {
    $atts = shortcode_atts(
        array(
            'grup' => '',
            'max'   => '10',
            'height' => '400',
            'weight' => '200',
        ),
        $atts
    );

    $js          = '<script type="text/javascript">slidesjs_callback.push(function () { $("#%s").slidesjs({ width: %s, height: %s }); });</script>';
    $start       = '<div class="slides" id="%s">';
    $item_format = '<img src="%s" title="%s" text="%s" />';

    $result = '';

    $the_query = new WP_Query(array(
        'post_type'      => 'slide',
        'posts_per_page' => $atts['max'],
        'team_tag'       => $atts['grup']
    ));

    while ($the_query->have_posts() ) {
        $the_query->the_post();
        $result .= sprintf($item_format, get_the_post_thumbnail_url(), get_the_title(), get_the_content());
    }

    if ($result == '') {
        return '<p>There are no images here<p>';
    }

    if (substr_count($result, ' />') == 1) {
        $temp = explode(' ', $result, 5);

        return sprintf('<h3>%s</h3><img src="%s" /><p>%s</p>', FormatImgStr($temp[2]), FormatImgStr($temp[1]), FormatImgStr($temp[3]));
    }

    wp_reset_postdata();

    $next_id = GetNextId();
    return sprintf($js, $next_id, $atts['height'], $atts['weight']) . sprintf($start, $next_id) . $result . '</div>';
}
add_shortcode('Slideshow', '\\slideshow\\Shortcode');

//Widget
class SlideshowWidget extends \WP_Widget {
	/**
     * Sets up the widgets name etc
     */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'SlideshowWidget',
			'description' => 'My Widget is awesome',
		);
		parent::__construct( 'SlideshowWidget', 'Slideshow', $widget_ops );
	}

	/**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
	public function widget($args, $instance) {
		// outputs the content of the widget

        $js          = '<script type="text/javascript">slidesjs_callback.push(function () { $("#%s").slidesjs({ width: %s, height: %s }); });</script>';
        $start       = '<div class="slides" id="%s">';
        $item_format = '<img src="%s" title="%s" text="%s" />';

        $result = '';

        $the_query = new WP_Query(array(
            'post_type'      => 'slide',
            'posts_per_page' => 10,
            'team_tag'       => $instance['grup']
        ));

        while ($the_query->have_posts() ) {
            $the_query->the_post();
            $result .= sprintf($item_format, get_the_post_thumbnail_url(), get_the_title(), get_the_content());
        }

        if ($result == '') {
            echo '<p>There are no images here<p>';
            return;
        }

        if (substr_count($result, ' />') == 1) {
            $temp = explode(' ', $result, 5);

            echo sprintf('<h3>%s</h3><img src="%s" /><p>%s</p>', FormatImgStr($temp[2]), FormatImgStr($temp[1]), FormatImgStr($temp[3]));
            return;
        }

        wp_reset_postdata();

        $next_id = GetNextId();
        echo sprintf($js, $next_id, $instance['weight'], $instance['height']) . sprintf($start, $next_id) . $result . '</div>';
	}

	/**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
	public function form($instance) {
		// outputs the options form on admin
        if($instance) {
            $grup   = esc_attr($instance['grup']);
            $height = esc_attr($instance['height']);
            $weight = esc_attr($instance['weight']);
        } else {
            $grup   = '';
            $height = '';
            $weight = '';
        }
?>
        <p>
            <label for="<?php echo $this->get_field_id('grup');?>">
                Grup:
            </label>
            <input id="<?=$this->get_field_id('grup');?>" name="<?=$this->get_field_name('grup');?>" type="text" value="<?=$grup;?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height');?>">
                Height:
            </label>
            <input id="<?=$this->get_field_id('height');?>" name="<?=$this->get_field_name('height');?>" type="text" value="<?=$height;?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('weight');?>">
                Weight:
            </label>
            <input id="<?=$this->get_field_id('weight');?>" name="<?=$this->get_field_name('weight');?>" type="text" value="<?=$weight;?>" />
        </p>
        <?php
	}

	/**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
        $instance = $old_instance;
        // Fields
        $instance['grup']   = strip_tags($new_instance['grup']);
        $instance['height'] = strip_tags($new_instance['height']);
        $instance['weight'] = strip_tags($new_instance['weight']);
        return $instance;
	}
}
add_action('widgets_init', function(){
	register_widget('\\slideshow\\SlideshowWidget');
});