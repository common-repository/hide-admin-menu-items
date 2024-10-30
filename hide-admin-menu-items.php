<?php
/**
* Plugin Name:        Hide Admin Menu Items
* Description:        Hide unnecessary admin menu items.
* Version:            1.0.0
* Requires at least:  4.7
* Requires PHP:       7.0
* Author:             Charles-Alexandre Laurent
* Author URI:         https://chlaurent.com
* Licence:            GPL v3 or later
* License URI:        https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain:        hide-admin-menu-items
* Domain Path:        /languages
*/

/*
Hide Admin Menu Items is free a software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Hide Admin Menu Items is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Hide Admin Menu Items. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'HideAdminMenuItems' ) ) {

  class HideAdminMenuItems
  {

    public function __construct() {
      add_action( 'init', array( $this, 'hami_load_textdomain' ) );
      add_action( 'admin_menu', array( $this, 'hami_add_settings_menu' ) );
      add_action( 'admin_init', array( $this, 'hami_register_settings' ) );
      add_action( 'admin_init', array( $this, 'hami_get_menu_items' ) );
      add_action( 'admin_init', array( $this, 'hami_hide_menu_items' ), 999 );
      add_action( 'admin_enqueue_scripts', array( $this, 'hami_admin_enqueue_style') );
    }

    /**
     * Load translations.
     */
    public function hami_load_textdomain() {
      load_plugin_textdomain( 'hide-admin-menu-items', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Add a new sub settings page in the options general admin menu.
     */
    public function hami_add_settings_menu() {
      add_submenu_page(
        'options-general.php',
        __( 'Menu items', 'hide-admin-menu-items' ),
        __( 'Hide items', 'hide-admin-menu-items' ),
        'manage_options',
        'hide-admin-menu-items',
        array( $this, 'hami_submenu_page_callback' ),
      );
    }

    /**
     * Hide Admin Menu Items page callback function
     */
    public function hami_submenu_page_callback() {
      ?>
      <div id="hami-wrap" class="wrap">
        <h2 class="hami-title"><?php echo esc_html( get_admin_page_title() ); ?></h2>
        <form method="POST" action="options.php">
          <?php
            settings_fields( 'hami_settings_group' );
            do_settings_sections( 'hide-admin-menu-items' );
            submit_button();
          ?>
        </form>
      </div>
      <?php
    }

    /**
     * Register settings and sections.
     */
    public function hami_register_settings() {

      register_setting( 'hami_settings_group',  'hami_settings' );

      add_settings_section(
        'hami_settings',
        __( 'General Settings', 'hide-admin-menu-items' ),
        array( $this, 'hami_settings_section_callback' ),
        'hide-admin-menu-items'
      );

      add_settings_field(
        'hami_hidden_items',
        __( 'Check the items to hide.', 'hide-admin-menu-items' ),
        array( $this, 'hami_hidden_items_setting_callback' ),
        'hide-admin-menu-items',
        'hami_settings'
      );

    }

    /**
     * Not needed at the moment.
     */
    public function hami_settings_section_callback() {}

    /**
     * Show the Hide Amin Menu Items activation field.
     */
    public function hami_hidden_items_setting_callback() {

      $menu_items = get_option( 'hami_menu_items' );
      $hami_settings = get_option( 'hami_settings' );
      $nbr_of_items = 0;

        echo '<div id="hami-list">';

          foreach ( $menu_items as $slug => $name ) {
            $filtered_name = preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $name );
            $redable_name = wp_strip_all_tags( $filtered_name );
            $nbr_of_items++;
            $input_name = 'hami_settings[hide-' . $slug . ']';
            $value = isset( $hami_settings['hide-' . $slug] ) ? $hami_settings['hide-' . $slug] : 0;
            ?>
            <p>
              <input type="checkbox" id="<?php esc_attr_e( $input_name ); ?>" name="<?php esc_attr_e( $input_name ); ?>" value="on" <?php checked( $value, 'on' ); ?> />
              <label for="<?php esc_attr_e( $input_name ); ?>">
                <?php esc_html_e( $redable_name ); ?>
                <span class="dashicons hami-status"></span>
              </label>
            </p>
            <?php

          }
          ?>
          <p><?php esc_html_e( "Number of items : ", "hide-admin-menu-items" ); ?>
            <span class="hami-count"><?php esc_html_e( $nbr_of_items ) ?></span>
            /
            <span class="hami-total"><?php esc_html_e( $nbr_of_items )?></span>
          </p>
          <?php

        echo '</div>';
    }

    /**
     * Get all admin menu items and update the list.
     */
    public function hami_get_menu_items() {

      if ( ! is_admin() ) {
        return;
      }

      if ( isset( $_GET['page'] ) && 'hide-admin-menu-items' === $_GET['page']  ) {

        global $menu;
        $menu_items = wp_list_pluck( $menu, 0, 2 );

        foreach ( $menu_items as $slug => $name ) {
          if ( 'options-general.php' == $slug || ! $name ) {
            unset( $menu_items[$slug] );
          }
        }

        update_option( 'hami_menu_items', $menu_items );

      }

    }

    /**
     * Hide the unnecessary menu items.
     */
    public function hami_hide_menu_items() {
      $hami_settings = get_option( 'hami_settings' );

      if ( ! empty( $hami_settings ) ) {
        //Settings loop - get slug and remove associated menu item.
        foreach ( $hami_settings as $setting => $value ) {
          $slug = str_replace( 'hide-', '', $setting );
          remove_menu_page( $slug );
        }
      }
    }

    /**
     * Load Hami plugin scripts.
     */
    public function hami_admin_enqueue_style() {
      if ( isset( $_GET['page'] ) && 'hide-admin-menu-items' === $_GET['page'] ) {
        wp_enqueue_script( 'hami-admin-script',  plugin_dir_url( __FILE__ ) . 'assets/js/hami.min.js', array('jquery'), '1.0.0', true );
        wp_enqueue_style( 'hami-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/hami.min.css' );
      }
    }

  }

  /**
   * Let's go  ! ☆彡
   */
  new HideAdminMenuItems();

}
