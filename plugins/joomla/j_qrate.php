<?php
/**
 * @version    0.9
 * @package    Qoorate Comments for Joomla
 * @author     Qoorate
 */

// no direct access
defined( '_JEXEC' ) or die( 'Resitricted access' );

jimport( 'joomla.plugin.plugin' );


class plgContentJ_qrate extends JPlugin 
{

    protected $plg_name = "j_qrate";

    function plgContentJ_qrate( &$subject, $params ) 
    {
        parent::__construct( $subject, $params );
    }

    // Joomla! 1.5
    function onPrepareContent( &$row, &$params, $page = 0 )
    {
        global $mainframe;

        if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOID", $_COOKIE ) ) 
        { 
            setcookie( "QOOID", uniqid( "QOO" ) ); // sessionid 
        }
        
        if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOTID", $_COOKIE ) ) 
        { 
            setcookie( "QOOTID", uniqid("QOOT"), time() + 630720000); // tracking id 
        }

        $this->renderQoorate( $row, $params, $page = 0 );

    }

   // Main function
   function renderQoorate( &$row, &$params, $page )
   {
       // Check if plugin is enabled
       if(JPluginHelper::isEnabled('content',$this->plg_name)==false) return;

       // Check necessary props before rendering
       if ( !$row->id ) return;

       // API
       $mainframe = &JFactory::getApplication();
       $document  = &JFactory::getDocument();

       // Requests
       $view = JRequest::getCmd('view');
       // Don't render if not an article?
       if ( $view != 'article' ) return;

       // get current article id
       $location = md5( $row->id );

       // Get plugin parameters
       $plugin = &JPluginHelper::getPlugin( 'content', $this->plg_name );
       $pluginParams = new JParameter( $plugin->params );
       $q_api_key = $pluginParams->get('qKey', '');
       $q_api_secret = $pluginParams->get('qSecret', '');
       $q_shortname = $pluginParams->get('qShortname', '');
       $q_base = $pluginParams->get('qBaseURI', '');
       $q_upload = $pluginParams->get('qUploadURI', '');
       $q_feed = $pluginParams->get('qFeedURI', '');
       $q_embed_uri = $pluginParams->get('qEmbedURI', '');
       $q_json_uri = $pluginParams->get('qJSON_URI', '');
       $q_api_uri = $pluginParams->get('qAPI_URI', '');
       $q_xhr_proxy = $pluginParams->get('qXHR_PROXY_URI', '');
       $q_xhr_upload = $pluginParams->get('qXHR_UPLOAD_URI', '');
       $q_post_len = $pluginParams->get('qPostLen', '');
       $q_flag_msg = $pluginParams->get('qFlagSuccess', '');
       $q_signIn_msg = $pluginParams->get('qSignIn', '');
       $q_signedIn_msg = $pluginParams->get('qSignedIn', '');
       $q_OK = $pluginParams->get('qOK', '');
       $q_cancel = $pluginParams->get('qCancel', '');
       $q_logout_msg = $pluginParams->get('qLogout', '');
       $q_contrib_txt = $pluginParams->get('qContribution', '');
       $q_contribs_txt = $pluginParams->get('qContributions', '');

       $q_proxy_uri = JURI::base().'plugins'.DS.'content'.DS.'proxy'.DS.'q_post.php';
        
	   $qoorate_embed = true;

       require_once(dirname(__FILE__).DS.'proxy'.DS.'q_post.conf.php');

       //var_dump($q_proxy_uri);

       // Set constants for q_post.php
       define( 'QOORATE_API_KEY', $q_api_key );
       define( 'QOORATE_API_SECRET', $q_api_secret );
       define( 'QOORATE_SHORTNAME', $q_shortname );
       define('QOORATE_BASE_URI', $q_base);
       define('QOORATE_UPLOADER_URI', $q_upload );
       define('QOORATE_FEED_URI', $q_feed);
       define('QOORATE_EMBED_URI', $q_embed_uri);
       define('QOORATE_JSON_URI', $q_json_uri);
       // Includes
       require_once(dirname(__FILE__).DS.'proxy'.DS.'q_post.php');

       // get scripts to add to head
       $q_data = qooratePrepareProxyCaller( 'json', $q_api_key ); 
       $q_data = json_decode($q_data);
       $q_head_scripts = $q_data->head;

       // Set the javascript configuration
       $document->addScriptDeclaration("
         var qoorateConfig = {
             QOORATE_URI: " . "'" . $q_base . "'" .",
             QOORATE_API_URI: " . "'" . $q_api_uri . "'" .",
             PROXY_URI: " . "'" . $q_proxy_uri  . "'" .",

             XHR_PROXY_URI: " . "'" . $q_xhr_proxy  . "'" .",
             XHR_UPLOAD_URI: " . "'" . $q_xhr_upload  . "'" .",
             POST_MAX_LEN: " . "'" . $q_post_len  . "'" ."
         };

         var qoorateLang = {
             FLAG_SUCCESS: 'Thank you for your feedback.',
             SIGNIN: 'Sign in via',
             SIGNEDIN: 'Signed in via',
             OK: 'OK',
             CANCEL: 'Cancel',
             LOGOUT: 'Log Out',
             CONTRIBUTION: 'Contribution',
             CONTRIBUTIONS: 'Contributions',
             SIGNIN_TO_CONTRIBUTE: 'Please sign in to make a contribution.',
             REMOVE: 'Remove',
             LINK: '1. Insert a link',
             TOPIC_COMMENT: 'Pose a Yes/No Question',
             COMMENT: 'Your Comment',
             IMAGE_COMMENT: 'Image Caption',
             POST_TO: 'Post to',
             REPLY_LINK_COMMENT: '2. Say something about your link',
             SHARE_COMMENT: 'comment about this share...',
             POST_BUTTON: 'Post',
             POST_TO_BUTTON: 'Post To',
             UPLOADER_NO_JAVASCRIPT: 'Please enable JavaScript to use file uploader.',
             SELECT_IMAGE: 'Select an Image',
             ATTACH_THUMBNAIL: 'Attach an Image Thumbnail',
             FLAG_ACTION_TYPES: [ [ '1', 'Spam' ], [ '2', 'Offensive' ], [ '3', 'Off Topic' ], [ '4', 'Disagree' ] ],
             SORT_ACTION_TYPES: [ [ '1', 'voting'], [ '2', 'recent'], [ '3', 'oldest'] ],
             LOGIN_TYPES: [ [ 'tw', 'Twitter', 'twitter' ], [ 'fb', 'Facebook', 'facebook' ], [ 'gp', 'Google+', 'googleplus' ] ]
         };
   ");

       foreach ( $q_head_scripts as $script => $attrs )
       {
           if ( $attrs[0] == 'link' )
           {
               $href = $attrs[1][0][1];
               $type = 'text/css';
               $media = $attrs[1][1][1];
               // add styles
               $document->addStyleSheet($href, $type, $media);

           } else if ( $attrs[0] == 'script' )
           {
               $src = $attrs[1][0][1];
               $type = $attrs[1][1][1];
               $document->addScript($src, $type);
           }
       }
       $row->text .= $q_data->content ;
   }
}
