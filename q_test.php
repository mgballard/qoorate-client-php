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
require_once('q_post.php'); 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Qoorate test (Brubeck)</title>
    <!-- These values should really come from a call -->
    <?php
    qooratePrepareProxyCaller('embed_head')
    ?>
</head>
<body>
    <section>
    <?php
    qooratePrepareProxyCaller('embed_content')
    ?>
    </section>
</body>
</html>
