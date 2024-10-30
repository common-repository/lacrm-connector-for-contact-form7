<?php
$usercode = get_option( 'lacrm_user_code' );
$api_token = get_option( 'lacrm_api_token' );
?>

<div class="wrap lacrm-form">
   <h1><?php _e( 'Integration with Less Annoying CRM', 'lacrmconnector' ); ?></h1>

   <div class="card" id="lacrm">
      <h2 class="title"><?php _e( 'LACRM', 'lacrmconnector' ); ?></h2>
      <div class="infobox">
         <?php _e( 'LACRM', 'lacrmconnector' ); ?><br>
         <a href="https://www.lessannoyingcrm.com" target="_blank">lessannoyingcrm.com</a></div>
      <br class="clear">

      <!-- API token -->
      <div class="lacrm-token">
         <p><label class="user-code"><?php _e( 'User Code ', 'lacrmconnector' ); ?></label>
            <input type="text" name="user-code" class="user-code-text" id="user-code-text" value="<?php echo esc_attr( $usercode ); ?>"/>
         </p>
         <p><label class="token"><?php _e( 'API Token', 'lacrmconnector' ); ?></label>
            <input type="text" name="token" class="token-text" id="token-text" placeholder="<?php echo $api_token !== '' ? _e( 'Currently Active', 'lacrmconnector' ) : 'Not Active' ?>" value=""/>
         </p>
         <p><input type="button" name="verify-token" id="verify-token" value="<?php _e( 'Verify', 'lacrmconnector' ); ?>"
                   class="button button-primary verify-token" />
            <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
         <input type="hidden" name="default-id" class="default-id" id="default-id" value="<?php echo rand( 100, 9999 ); ?>"/>
         <!-- set nonce -->
         <input type="hidden" name="lacrm-integation-nonce" id="lacrm-integation-nonce" value="<?php echo wp_create_nonce( 'lacrm-integation-nonce' ); ?>" />
         <p id="validation-message"></p>

      </div>
      <!-- End of API token -->
   </div>

</div>