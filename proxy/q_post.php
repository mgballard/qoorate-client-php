<?PHP
// pull in our configuration
if (file_exists('q_post.conf.php')) {
    require_once 'q_post.conf.php';
}

// see if we are a web request or a local include
if(isset($qoorate_embed)){
    error_log('we are an embed, let us be called manually');
} else {
    error_log('web request');
    qooratePrepareProxyCaller(null, null);
}

// url-ify an array of fields
function qoorate_urlify_fields($fields) {
    $fields_string = '';
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
    $fields_string = rtrim($fields_string,'&');
    return $fields_string;    
}

// figure out our url for the proxy call
function qooratePrepareProxyCaller($action, $short) {
    $baseUrl = QOORATE_EMBED_URI; 

    if (isset($short)) {
        // remove any hashes in the url
        $location = md5($short);
    }
    
    // Our clients unique key and secret for qoorate api
    $key = QOORATE_API_KEY;
    $secret = QOORATE_API_SECRET;


    $url = ''; // URL to request with proxy
    
    // Some control flow flags
    $is_embed = false;
    $is_upload = false;
    $is_post = false;
    if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $is_post = true;
    }
    // set our flags and get our action
    // all requests need an action
    if(isset($action)){
        // we are a the first call to a page
        $is_embed = true;
    } else if(isset($_POST['action'])) {
        // we are a jquery request
        $action = $_POST['action'];
        $is_post = true;
    } else if(isset($_GET['action'])) {
        // we don't usually get here
        // the client should use JSONP for AJAX get requests
        // or access resources directly from api server
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
            $is_upload = true;
        } else {
            $baseUrl = QOORATE_FEED_URI;
        }
    
        $get_vars = qoorate_urlify_fields($_GET);
        $url = $baseUrl . ($get_vars =='' ? '' : '?' . $get_vars);
        error_log ($url);
    } else if ($is_embed) {
        error_log ("embed action set:" . $action);
        $url = $baseUrl . '?action='. $action . '&q_api_key=' . $key . '&q_api_secret=' . $secret . '&location=' . $location;
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
    qoorateProxyCaller($url, $is_post, $is_upload);
}

function qoorateProxyCaller($url, $is_post, $is_upload) {
    
    // Change these configuration options if needed, see above descriptions for info.
    $valid_url_regex = '/.*/';

    $file_path = '';
    $file_name = '';

    $header = '';
    $cookie = null;
    $contents = '';
    $status = null;
    
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

        if ($is_upload) {
            // we are a file upload
            // save our file to the temp directory
            // TODO: This only handles new browser XHR requests, not traditional file upload
            $temp_file_name = tempnam(sys_get_temp_dir(), 'QOO');
            $file_handle = fopen($temp_file_name, 'wb');
            $file_name = $_REQUEST['qqfile'];
            $input = fopen("php://input", "rb");
            stream_copy_to_stream($input, $file_handle);
            fclose($input);
            fclose($file_handle);
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

        $post = "";
        if($is_upload) {
            // get our mime type from the file, not the extension
            $mime_type = mime_content_type ( $temp_file_name );

            // read the data from the file
            $file_handle = fopen($temp_file_name, 'rb');
            while (!feof($file_handle)) {
                $post .= fread($file_handle, 4096);
            }
            fclose ($file_handle);

            // delete the file
            unlink($temp_file_name);

            error_log($post);
            error_log(strlen($post));
        }else {
            // prepare our post data for the content
            $post = qoorate_urlify_fields($_POST);
        }

        // Make our request with curl
        $ch = curl_init( $url );
        if ($is_post) {
            // we are a post request
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
