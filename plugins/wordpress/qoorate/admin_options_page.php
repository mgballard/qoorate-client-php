<?php
/*
Template Name: Profiles
*/
?>
<?php 
	global $qoorate;
	require_once( 'admin_options.php' );
	$admin_options = new Qoorate_Admin_Options( $qoorate );	
	//$profile_field_instances = $qoorate->get_profile_field_instances();
	//$profile_roles = $qoorate->get_profile_field_roles();
	//$profile_field_types = $qoorate->get_profile_field_types();

?>
<h1 class="profile-fields">Qoorate Options</h1>
<form action="<?php the_permalink(); ?>" id="profileForm" method="post">
	<h2 class="profile-fields">Configuration</h2>
	<ul>
		<li>
			<div class="config-container">
				<div id="qoorate-api-key-container">
					<label for="qorate-api-key">API Key <span class="qoorate-api-key">(required)</span></label>
					<input type="text" name="qoorate-api-key" id="api-key" value="<?php echo( $admin_options->api_key ); ?>" />
			 		<?php if ( ! ('' == $admin_options->api_key_error) ) : ?>
			 			<div class="error"><?php echo($admin_options->api_key_error); ?></div>
			 		<?php endif; ?>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
				<div id="qoorate-api-secret-container">
					<label for="qoorate-api-secret">API Secret <span class="qoorate-api-secret">(required)</span></label>
					<input type="text" name="qoorate-api-secret" id="qoorate-api-secret" value="<?php echo( $admin_options->api_secret ); ?>" />
			 		<?php if ( ! ('' == $admin_options->api_secret_error) ) : ?>
			 			<div class="error"><?php echo($admin_options->api_secret_error); ?></div>
			 		<?php endif; ?>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
				<div id="qoorate-api-shortname-container">
					<label for="qoorate-api-shortname">API Shortname <span class="qoorate-api-shortname">(required)</span></label>
					<input type="text" name="qoorate-api-shortname" id="qoorate-api-shortname" value="<?php echo( $admin_options->api_shortname ); ?>" />
			 		<?php if ( ! ('' == $admin_options->api_shortname_error) ) : ?>
			 			<div class="error"><?php echo($admin_options->api_shortname_error); ?></div>
			 		<?php endif; ?>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
		</li>
		<li>
			<div class="options-save-container">
				<button id="options-save-button" type="submit">Update</button>
				<div class="clearfix"></div>
			</div>
		</li>
	</ul>
	<input type="hidden" name="submitted" id="submitted" value="true" />
</form>