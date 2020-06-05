<?php

class Slick_FilmStrip_Widget extends WP_Widget
{
    /**
     * based on https://www.wpexplorer.com/create-widget-plugin-wordpress/

     * The widget simply creates a container that the Slick script will populate
     * after the page loads.
     */

    public function __construct()
    {
        parent::__construct(
            'slick_filmstrip_widget',
            __('Slick FilmStrip Widget', 'text_domain'),
            array('customize_selective_refresh' => true)
        );
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => '',
        );
        extract(wp_parse_args((array) $instance, $defaults));
        ?>
    <?php // Widget Title ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Widget Title', 'text_domain');?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
    <?php
}

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = isset($new_instance['title']) ? wp_strip_all_tags($new_instance['title']) : '';
        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args);
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo '<div class="slick-widget slick-film-strip">';
        echo '</div>';
        echo $after_widget;
    }
}

class Slick_Game_Widget extends WP_Widget
{
    /**
     * based on https://www.wpexplorer.com/create-widget-plugin-wordpress/

     * The widget simply creates a container that the Slick script will populate
     * after the page loads.
     */

    public function __construct()
    {
        parent::__construct(
            'slick_game_widget',
            __('Slick Game Widget', 'text_domain'),
            array('customize_selective_refresh' => true)
        );
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => '',
        );
        extract(wp_parse_args((array) $instance, $defaults));
        ?>
    <?php // Widget Title ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Widget Title', 'text_domain');?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
    <?php
}

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = isset($new_instance['title']) ? wp_strip_all_tags($new_instance['title']) : '';
        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args);
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo '<div class="slick-widget slick-game-panel">';
        echo '</div>';
        echo $after_widget;
    }
}

class Slick_NextUp_Widget extends WP_Widget
{
    /**
     * based on https://www.wpexplorer.com/create-widget-plugin-wordpress/

     * The widget simply creates a container that the Slick script will populate
     * after the page loads.
     */

    public function __construct()
    {
        parent::__construct(
            'slick_nextup_widget',
            __('Slick NextUp Widget', 'text_domain'),
            array('customize_selective_refresh' => true)
        );
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => '',
        );
        extract(wp_parse_args((array) $instance, $defaults));
        ?>
    <?php // Widget Title ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Widget Title', 'text_domain');?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
    <?php
}

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = isset($new_instance['title']) ? wp_strip_all_tags($new_instance['title']) : '';
        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args);
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo '<div class="slick-widget slick-next-up">';
        echo '</div>';
        echo $after_widget;
    }
}

function register_slick_widgets()
{
    register_widget('Slick_FilmStrip_Widget');
    register_widget('Slick_Game_Widget');
    register_widget('Slick_NextUp_Widget');
}

add_action('widgets_init', 'register_slick_widgets');
