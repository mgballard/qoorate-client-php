<?PHP
//if(!isset($_SESSION)) {
//    session_start();
//}
require_once 'q_post.conf.php';

if(isset($qoorate_embed)){
    error_log('we are an embed, let us be called manually');
} else {
    error_log('proxy call');
    qooratePrepareProxyCaller(null);
}

//url-ify an array of fields
function qoorate_urlify_fields($fields) {
    $fields_string = '';
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
    $fields_string = rtrim($fields_string,'&');
    return $fields_string;    
}

function qooratePrepareProxyCaller($action) {
    $baseUrl = QOORATE_EMBED_URI; 
    // remove &q=loggoff 
    $p = $_SERVER['REQUEST_URI'];
    $a_p = explode ( '#' , $p );
    $p = $a_p[0];
    
    $page = md5($p);
    
    $key = '1';
    $secret = '2';
    $short = 'sample';
    $is_post = false;
    $is_embed = false;
    $url = '';
    
    if(isset($action)){
        $is_embed = true;
    } else if(isset($_POST['action'])) {
        $action = $_POST['action'];
        $is_post = true;
    } else if(isset($_GET['action'])) {
        $action = $_GET['action'];
    }
    
    if($is_post){
        error_log ("post action set");
        error_log ($_POST["action"]);
        $get_vars = '';
    
        if($action == 'embed_content' || $action == 'embed_html'){
            $baseUrl = QOORATE_EMBED_URI; 
        } else if($action == 'uploader'){
            $baseUrl = QOORATE_UPLOADER_URI;
        } else {
            $baseUrl = QOORATE_FEED_URI;
        }
    
        $get_vars = qoorate_urlify_fields($_GET);
        $url = $baseUrl . ($get_vars =='' ? '' : '?' . $get_vars);
        error_log ($url);
    } else if ($is_embed) {
        error_log ("embed action set:" . $action);
        $url = $baseUrl . '?action='. $action . '&q_api_key=' . $key . '&q_api_secret=' . $secret . '&q_short_name=' . $short . '&page=' . $page;
    }else{
        error_log ("get action set");
        $get_vars = '';
        if($action == 'embed_content' || $action == 'embed_html'){
            $baseUrl = QOORATE_EMBED_URI; 
        } else if($_GET['action'] == 'uploader'){
            $baseUrl = QOORATE_UPLOADER_URI;
        } else {
            $baseUrl = QOORATE_FEED_URI;
        }
        
        $get_vars = qoorate_urlify_fields($_GET);
        $url = $baseUrl . ($get_vars =='' ? '' : '?' . $get_vars);
        error_log ($url);
    }
    qoorateProxyCaller($url);
}

function qoorateProxyCaller($url) {
    
    // Change these configuration options if needed, see above descriptions for info.
    $enable_jsonp    = false;
    $enable_native   = true;
    $valid_url_regex = '/.*/';

    $is_multipart = false;
    $file_path = '';
    $file_name = '';
    $file_content_array = array();

    $header = '';
    $contents = '';
    $temp_file_name = null;
    if ( !$url ) {

        // Passed url not specified.
        $contents = 'ERROR: url not specified';
        $status = array( 'http_code' => 'ERROR' );

    } else if ( !preg_match( $valid_url_regex, $url ) ) {

        // Passed url doesn't match $valid_url_regex.
        $contents = 'ERROR: invalid url';
        $status = array( 'http_code' => 'ERROR' );

    } else {
        error_log($url);
        if ( isset($_GET['action']) && $_GET['action']=='uploader' && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            // we are a file upload
            $is_multipart = true;

            $temp_file_name = tempnam(sys_get_temp_dir(), 'QOO');
            $file_handle = fopen($temp_file_name, 'wb');
            
            $file_name = $_REQUEST['qqfile'];
            $input = fopen("php://input", "rb");
            
            //$file_handle = tmpfile();
            stream_copy_to_stream($input, $file_handle);
            fclose($input);
            // keep us open
            fclose($file_handle);

            /* 
            $post_fields = array(
                $file_name => "@file_$path;type=$mime_type"
            );
            error_log( print_r($post_fields, true) );
            error_log( "mime_type: $mime_type" );
            
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            */
        }

        // proxy our cookies
        if ( isset( $_COOKIE ) ) {
            $cookie = array();
            foreach ( $_COOKIE as $key => $value ) {
                $cookie[] = $key . '=' . $value;
            }
            if ( array_key_exists('send_session', $_GET) && $_GET['send_session'] ) {
                $cookie[] = SID;
            }
            $cookie = implode( '; ', $cookie );

        }

        $ch = curl_init( $url );
        $post = "";
        if($is_multipart) {
            $mime_type = mime_content_type ( $temp_file_name );
            $file_size = filesize($temp_file_name);
            $content_length = $file_size + strlen($file_name);
            $file_handle = fopen($temp_file_name, 'rb');
            $data = '';
            //$data .= "--" . $mime_boundary . $eol;
            while (!feof($file_handle)) {
                //$data .= chunk_split(base64_encode(fread($file_handle, 1024))) . $eol;
                $data .= fread($file_handle, 4096);
            }
            fclose ($file_handle);
            unlink($temp_file_name);
            error_log($data);
            error_log(strlen($data));
            $post = $data;
        }else {
            $post = qoorate_urlify_fields($_POST);
        }

        if ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
            // we are a normal post request
            error_log(print_r($_POST, true));
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
        }

        if (isset($cookie)) {
            curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
        }

        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_HEADER, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
        curl_setopt( $ch, CURLOPT_USERAGENT, array_key_exists('user_agent', $_GET) ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT'] );

        $status = curl_getinfo( $ch );

        $response = curl_exec( $ch );

        error_log( $response );

        list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', $response, 2 );

        curl_close( $ch );

        print $contents;
    }
}
