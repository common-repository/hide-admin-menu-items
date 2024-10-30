<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  die;
}
//delete hami options
delete_option( 'hami_menu_items' );
delete_option( 'hami_settings' );