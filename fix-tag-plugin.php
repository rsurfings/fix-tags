<?php

/**
 * @package Sharkcoders
 */
/*
Plugin Name: Sharkcoders FixTags
Plugin URI: https://sharkcoders.com/
Description: Sharkcoders ink.
Version: 0.0.1
Author: Sharkcoders
Author URI: https://sharkcoders.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: sharkcoders
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
Foundation, Inc., Montezuma Avenu, 315, Itanhaem, SP 1174000, BR.

Copyright 2020/2021 Sharkcoders, Inc.
*/
class FixTag_Plugin
{

    protected $url;

    public function __construct()
    {
        // Hook into the admin menu
        add_action('admin_menu', array($this, 'create_plugin_settings_page'));

        // custom css and js
        add_action('admin_enqueue_scripts', array($this, 'cstm_css_and_js'));

        $this->url = wp_parse_url(home_url($_SERVER['REQUEST_URI']));
       
        $tags =  explode(", ", get_option('field_tags'));
        // Hook the the_title filter hook, run the function named change_title
        add_filter('the_title', function ($title) use ($tags) {
            if($this->url['path'] != '/'){
                return $title;
            }else{
                return $this->change_title($title, $tags);
            }
        }, 10, 1);

        
    }
    function change_title($title, $tags)
    {

        foreach ($tags as $tag) {

            //searching for the exacly needle tag
            if (preg_match("/\b$tag\b/", $title)) {
                $pos = strpos($title, $tag);
                $replaced = ltrim(substr($title, strlen($tag)));
                $title  = $replaced . " " . '(' . $tag . ')';
            }
        }

        return ucfirst($title);
    }
    function cstm_css_and_js($hook)
    {
        wp_enqueue_style('boot_css', plugins_url('_inc/tagify.css', __FILE__));
        wp_enqueue_script('boot_js_jquery', 'https://code.jquery.com/jquery-1.12.4.min.js');
        wp_enqueue_script('boot_js', plugins_url('_inc/jQuery.tagify.min.js', __FILE__));
        wp_enqueue_script('boot_js_tagify', plugins_url('_inc/form.js', __FILE__));
    }
    public function create_plugin_settings_page()
    {
        // Add the menu item and page
        $page_title = 'Fix Tag Settings Page';
        $menu_title = 'Fix Tag Plugin';
        $capability = 'manage_options';
        $slug = 'smashing_fields';
        $callback = array($this, 'plugin_settings_page_content');
        $icon = 'dashicons-admin-plugins';
        $position = 100;

        add_options_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
    }
    public function plugin_settings_page_content()
    {
        if ($_POST['updated'] === 'true') {
            $this->handle_form();
        } ?>
        <div class="wrap">
            <h2>Fix Tag Settings Page</h2>
            <form method="POST">
                <input type="hidden" name="updated" value="true" />
                <?php wp_nonce_field('field_update', 'field_form'); ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><label for="tags">Tags</label></th>
                            <td>
                                <input type="text" name='tags' id="tags" placeholder='write some tags' value="<?php echo get_option('field_tags'); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                </p>
            </form>
        </div> <?php

            }

            public function validate($tags)
            {
                return true;
            }

            public function handle_form()
            {
                if (!isset($_POST['field_form']) || !wp_verify_nonce($_POST['field_form'], 'field_update')) { ?>
            <div class="error">
                <p>Sorry, your nonce was not correct. Please try again.</p>
            </div> <?php
                    exit;
                } else {

                    $tags = implode(', ', array_column(json_decode(stripslashes($_POST['tags']),true), 'value'));

                    if ($this->validate($tags)) {
                        update_option('field_tags', $tags);
                    ?>
                <div class="updated">
                    <p>Your tags were saved!</p>
                </div> <?php
                    } else { ?>
                <div class="error">
                    <p>Your tags is invalid.</p>
                </div> <?php
                    }
                }
            }
        }
        new FixTag_Plugin();
