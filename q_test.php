<?php
// This is a simple test file
if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOID", $_COOKIE ) ) {
    setcookie( "QOOID", uniqid( "QOO" ) ); // sessionid
}
if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOTID", $_COOKIE ) ) {
    setcookie( "QOOTID", uniqid("QOOT"), time() + 630720000); // tracking id
}
?>
<?php
$qoorate_embed = true;
if (file_exists('q_post.conf.php')) {
    include 'q_post.conf.php';
}
require_once('q_post.php'); 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Qoorate test (Brubeck)</title>
    <!-- These values should really come from a call -->
    <?php
    echo(qooratePrepareProxyCaller('embed_head', 'page1'));
    ?>
</head>
<body>
    <section>
    <?php
    echo(qooratePrepareProxyCaller('embed_content', 'page1'));
    ?>
    </section>
</body>
</html>
