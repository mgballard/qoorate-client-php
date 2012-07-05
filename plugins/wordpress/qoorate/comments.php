<?php
define( 'MYPLUGIN_PATH', plugin_dir_path(__FILE__) );
if (file_exists(MYPLUGIN_PATH . 'q_post.conf.php')) {
    include  MYPLUGIN_PATH . 'q_post.conf.php';
}
define('QOORATE_SHORTNAME', get_option('qoorate_api_shortname'), 'short_name');
$qoorate_embed = true;
require_once( MYPLUGIN_PATH . 'q_post.php');  
echo(qooratePrepareProxyCaller('embed_head', null));
echo(qooratePrepareProxyCaller('embed_content', null));
?>