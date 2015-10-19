<?php
/*
Plugin Name: CTwiz feed
Plugin URI: http://wordpress.org/extend/plugins/ctwiz-feed/
Description: Adds a new type of feed you can subscribe to. http://example.com/feed/ctwiz or http://example.com/?feed=ctwiz to anywhere you get a JSON form.
This is based on the wokamoto's feed-json plugin
Author: CTwiz LTD. 
Version: 1
Author URI: http://ctwiz.com/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2011-2014 (email : wokamoto1973@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$ctwiz_feed = ctwiz_feed::get_instance();
$ctwiz_feed->init();

register_activation_hook(__FILE__, array($ctwiz_feed, 'activation'));
register_deactivation_hook(__FILE__, array($ctwiz_feed, 'deactivation'));

class ctwiz_feed
{
    static $instance;
    const  TEMPLATE = 'ctwiz-feed.php';

    public function __construct()
    {
    }

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('init', array($this, 'add'));
        add_action('do_feed_ctwiz', array($this, 'render'), 10, 1);
        add_filter('template_include', array($this, 'template'));
//		add_filter( 'query_vars', array( $this, 'add_query_vars') );
    }

    public function activation()
    {
        $this->add();
        flush_rewrite_rules();
    }

    public function deactivation()
    {
        global $wp_rewrite;
        $feeds = array();
        foreach ($wp_rewrite->feeds as $feed) {
            if ($feed !== 'json') {
                $feeds[] = $feed;
            }
        }
        $wp_rewrite->feeds = $feeds;
        flush_rewrite_rules();
    }

//	public function add_query_vars($qvars) {
//		$qvars[] = 'callback';
//		$qvars[] = 'limit';
//		return $qvars;
//	}

    public function add()
    {
        add_feed('ctwiz', array($this, 'render'));
    }

    public function render()
    {
        load_template($this->template(dirname(__FILE__) . '/template/' . self::TEMPLATE));
    }

    public function template($template)
    {
        $template_file = false;
        if (get_query_var('feed') === 'json') {
            if (function_exists('get_stylesheet_directory') && file_exists(get_stylesheet_directory() . '/' . self::TEMPLATE)) {
                $template_file = get_stylesheet_directory() . '/' . self::TEMPLATE;
            } elseif (function_exists('get_template_directory') && file_exists(get_template_directory() . '/' . self::TEMPLATE)) {
                $template_file = get_template_directory() . '/' . self::TEMPLATE;
            } elseif (file_exists(dirname(__FILE__) . '/template/' . self::TEMPLATE)) {
                $template_file = dirname(__FILE__) . '/template/' . self::TEMPLATE;
            }
        }

        $template_file = ($template_file !== false ? $template_file : $template);
        return apply_filters('feed-json-template-file', $template_file);
    }
}
