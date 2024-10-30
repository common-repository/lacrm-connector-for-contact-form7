<?php
$lacrm_connector_service = new Lacrm_Connector_Service();
$form_name = $lacrm_connector_service->fetch_form_name();
$form_id = $post->ID;

$usercode = get_option( 'lacrm_user_code' );
$api_token = get_option( 'lacrm_api_token' );

if ( isset( $_GET[ 'post' ] ) && ! empty( $_GET[ 'post' ] ) ) {
   $form_id = intval( sanitize_text_field( $_GET[ 'post' ] ) );
   $form_data = $lacrm_connector_service->get_form_data( $form_id );
   if ( ! empty( $form_data ) ) {
      $contact_info = json_decode( $form_data->contact_info );
      $custom_fields = json_decode( $form_data->custom_fields );
      $pipeline_info = json_decode( $form_data->pipeline_info );
      $notes_info = $form_data->notes;
      $pipeline_fields = json_decode( $form_data->pipeline_fields );
      $task = unserialize( $form_data->tasks );
   }
}
?>
<div class="wrap lacrm-form">
   <h2><?php echo esc_html( __( 'Lacrm Settings', 'lacrmconnector' ) ); ?></h2>
   <?php if ( get_option( 'lacrm_verify_token' ) == 'invalid' ) { ?>
      <p class="integartion-notification"><?php echo esc_html( __( 'LACRM Integation is not valid . Please configure with correct information', 'lacrmconnector' ) ); ?></p>  
   <?php } ?>
   <fieldset>
      <legend><?php echo esc_html( __( "In the following fields, you can use these mail tags:", 'lacrmconnector' ) ); ?><br />
         <?php
         $post->suggest_mail_tags();
         ?>
      </legend>
   </fieldset>

   <!-- Basic fields -->
   <div class="basic-fields">
      <p class="basic-field-header" ><?php _e( 'Basic Fields', 'lacrmconnector' ); ?></p> 
      <div class="basic-fields-attributes">
         <p>
            <label class="field-label"><?php _e( 'Name', 'lacrmconnector' ); ?></label>
            <input type="text" name="basic-field-name" id="basic-field-name" 
                   placeholder="<?php _e( 'your-name', 'lacrmconnector' ); ?>"
                   value="<?php
         if ( isset( $contact_info->FullName ) ) {
            echo esc_attr( $fullname = $contact_info->FullName );
         } else {
            esc_attr( $fullname = '' );
         }
         ?>" />
         </p>    
         <p>
            <label class="field-label"><?php _e( 'Email', 'lacrmconnector' ); ?></label>
            <input type="text" name="basic-field-email" id="basic-field-email" 
                   placeholder="<?php _e( 'your-email', 'lacrmconnector' ); ?>" 
                   value="<?php
                   if ( isset( $contact_info->Email ) ) {
                      echo esc_attr( $email = $contact_info->Email );
                   } else {
                      esc_attr( $email = '' );
                   }
         ?>" />
         </p>
         <p>
            <label class="field-label"><?php _e( 'Phone', 'lacrmconnector' ); ?></label>
            <input type="text" name="basic-field-phone" id="basic-field-phone" 
                   placeholder="<?php _e( 'your-phone', 'lacrmconnector' ); ?>" 
                   value="<?php
                   if ( isset( $contact_info->Phone ) ) {
                      echo esc_attr( $phone = $contact_info->Phone );
                   } else {
                      esc_attr( $phone = '' );
                   }
         ?>" />
         </p>
         <p>
            <label class="field-label"><?php _e( 'Company', 'lacrmconnector' ); ?></label>
            <input type="text" name="basic-field-company" id="basic-field-company"
                   placeholder="<?php _e( 'your-company', 'lacrmconnector' ); ?>" 
                   value="<?php
                   if ( isset( $contact_info->CompanyName ) ) {
                      echo esc_attr( $compnay = $contact_info->CompanyName );
                   } else {
                      esc_attr( $compnay = '' );
                   }
         ?>" />
         </p>
      </div>
   </div>
   <!-- End of Basic fields -->

   <!-- Custom fields -->
   <div class="custom-fields">
      <p class="custom-fields-header"><?php _e( 'Custom Fields', 'lacrmconnector' ); ?>
         <span><a href="https://www.lessannoyingcrm.com/account/settings/fields.php" target="_blank" class="help-link"> Where do i get LACRM Custom Contact Field Name ? </a></span>
      </p> 
      <p>
         <label><?php _e( 'LACRM Custom Contact Field Name', 'lacrmconnector' ); ?></label>
         <label><?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?></label>
      </p>
      <br class="clearfix">
      <div class="contact-custom-fields">
         <p class="lacrm-wrapper-hidden">
            <input type="text" name="field-name[]" class="field-name" id="field-name" placeholder="<?php _e( 'LACRM Custom Contact Field Name', 'lacrmconnector' ); ?>"/>
            <input type="text" name="field-value[]" class="field-value" id="field-value" placeholder="<?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?>"/>
            <span class="icon-remove remove-cutom-field">
               <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
            </span>
         </p>
         <?php
         if ( isset( $_GET[ 'post' ] ) && ( ! empty( $form_data ) ) ) {
            foreach ( $custom_fields as $key => $value ) {
               ?>
               <p class="lacrm-wrapper">
                  <input type="text" name="field-name[]" class="field-name" id="field-name"
                         placeholder="<?php _e( 'LACRM Custom Contact Field Name', 'lacrmconnector' ); ?>"
                         value=<?php echo esc_attr( $key ); ?> />
                  <input type="text" name="field-value[]" class="field-value" id="field-value" 
                         placeholder="<?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?>"
                         value="<?php echo esc_attr( $value ); ?>" />
                  <span class="icon-remove remove-cutom-field">
                     <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
                  </span>
               </p>
               <?php
            }
         }
         ?>
         <br class="clearfix">
         <!-- add new custom fields -->
         <input type="button" name="add-new-field" id="add-new-field" value="<?php _e( 'Add custom field', 'lacrmconnector' ); ?>"
                class="button button-secondary add-new-field" />
      </div>
   </div>
   <!-- End of Custom fields -->

   <!-- pipeline -->
   <div class="pipeline">
      <p class="pipeline-header"><?php _e( 'Pipeline', 'lacrmconnector' ); ?></p>
      <div class="pipeline-attributes">
         <p>
            <label class="pipeline-label"><?php _e( 'Pipeline ID', 'lacrmconnector' ); ?></label>
            <input type="text" name="pipeline-id" id="pipeline-id" 
                   value="<?php
         if ( isset( $pipeline_info->PipelineId ) ) {
            echo esc_attr( $id = $pipeline_info->PipelineId );
         } else {
            esc_attr( $id = '' );
         }
         ?>" />
         </p> 
         <p>
            <label class="pipeline-label"><?php _e( 'Status ID', 'lacrmconnector' ); ?></label>
            <input type="text" name="pipeline-status" id="pipeline-status" 
                   value="<?php
                   if ( isset( $pipeline_info->StatusId ) ) {
                      echo esc_attr( $status = $pipeline_info->StatusId );
                   } else {
                      esc_attr( $status = '' );
                   }
         ?>" />
         </p>
         <p>
            <label class="pipeline-label"><?php _e( 'Priority', 'lacrmconnector' ); ?></label>
            <input type="text" name="pipeline-priority" id="pipeline-priority" 
                   value="<?php
                   if ( isset( $pipeline_info->Priority ) ) {
                      echo esc_attr( $priority = $pipeline_info->Priority );
                   } else {
                      esc_attr( $priority = '' );
                   }
         ?>" />
         </p>
      </div>
   </div>
   <!-- End of pipeline -->

   <!-- Add Notes -->
   <div class="notes">
      <p class="notes-header"><?php _e( 'Notes', 'lacrmconnector' ); ?></p>
      <div class="contact-notes"> 
         <textarea name="note-message" id="note-message" cols="50" rows="10" ><?php
                   if ( isset( $_GET[ 'post' ] ) && ( ! empty( $form_data ) ) ) {
                      if ( isset( $notes_info ) ) {
                         echo esc_textarea( $notes_info );
                      } else {
                         esc_attr( $notes = '' );
                      }
                   }
         ?></textarea>  
      </div>
   </div>
   <!-- End of Add Notes -->

   <!-- Add custom pipeline fields -->
   <div class="custom-pipeline-fields">
      <p class="custom-pipeline-header"><?php _e( 'Custom Pipeline Fields', 'lacrmconnector' ); ?> 
         <span><a href="https://www.lessannoyingcrm.com/account/api/" target="_blank" class="help-link"> Where do i get LACRM Field ID ? </a></span>
      </p>
      <p>
         <label><?php _e( 'LACRM Field Id', 'lacrmconnector' ); ?></label>
         <label><?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?></label>
      </p>
      <br class="clearfix">
      <div class="pipeline-fields">
         <p class="lacrm-wrapper-hidden">
            <input type="text" name="custom-pipeline-id[]" id="custom-pipeline-id" placeholder="<?php _e( 'LACRM Field Id', 'lacrmconnector' ); ?>"/>
            <input type="text" name="pipeline-value[]" id="pipeline-value" placeholder="<?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?>"/>
            <span class="icon-remove remove-custom-pipeline-fields">
               <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
            </span>
         </p>
         <?php
         if ( isset( $_GET[ 'post' ] ) && ( ! empty( $form_data ) ) ) {
            foreach ( $pipeline_fields as $key => $value ) {
               ?>
               <p class="lacrm-wrapper">
                  <input type="text" name="custom-pipeline-id[]" id="custom-pipeline-id" 
                         placeholder="<?php _e( 'LACRM Field Id', 'lacrmconnector' ); ?>"
                         value=<?php echo esc_attr( $key ); ?> />
                  <input type="text" name="pipeline-value[]" id="pipeline-value" 
                         placeholder="<?php _e( 'CF7 Mail Tag', 'lacrmconnector' ); ?>"
                         value="<?php echo esc_attr( $value ); ?>" />
                  <span class="icon-remove remove-custom-pipeline-fields">
                     <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
                  </span>
               </p>
               <?php
            }
         }
         ?>
         <br class="clearfix">
         <!-- add new note -->
         <input type="button" name="add-custom-pipeline-fields" id="add-custom-pipeline-fields" value="<?php _e( 'Add Custom Fields', 'lacrmconnector' ); ?>"
                class="button button-secondary add-custom-pipeline-fields" />
      </div>
   </div>
   <!-- End of Add additional custom fields -->

   <!-- Add task -->
   <div class="tasks">
      <p class="tasks-header"><?php _e( 'Tasks', 'lacrmconnector' ); ?><span class="get-user-id help-link">Where do i get LACRM User ID ?<span class='hover-data'><?php _e(' In LACRM, go to','lacrmconnector' )?> <a href=" https://www.lessannoyingcrm.com/account/settings/users.php" target="_blank" class="user-link"><?php _e( 'Manage Users', 'lacrmconnector' )?></a> <?php _e( 'page and click on "Edit this user" next to the targeted user name. Grab the number after "UserId=" from the URL of the page that opens.','lacrmconnector') ?></span></span></p>
      <p class="task-title">
         <label><?php _e( 'LACRM user ID', 'lacrmconnector' ); ?></label>
         <label><?php _e( 'Due', 'lacrmconnector' ); ?>&nbsp;&nbsp;<a href="#" title="<?php _e( 'In how many days from today is this task due?', "lacrmconnector" );
         ?>"><span><img src="<?php echo LACRM_CONNECTOR_URL . '/assets/img/help.png'; ?>" class="help-icon"/></span></a>
         </label>
         <label><?php _e( 'Task Description', 'lacrmconnector' ); ?></label>
      </p>
      <br class="clearfix">
      <div class="task-fields">
         <p class="lacrm-wrapper-hidden">
            <input type="text" name="task-assign-to[]"  id="task-assign-to" placeholder="<?php _e( 'LACRM user ID', 'lacrmconnector' ); ?>" />
            <input type="text" name="task-due-date[]" id="task-due-date" placeholder="<?php _e( 'Due (today + X days)', 'lacrmconnector' ); ?>" />
            <input type="text" name="task-description[]" id="task-description" placeholder="<?php _e( 'Task description', 'lacrmconnector' ); ?>"/>
            <span class="icon-remove remove-task-fields">
               <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
            </span>
         </p>
         <?php
         if ( isset( $_GET[ 'post' ] ) && ( ! empty( $form_data ) ) ) {
            foreach ( $task as $value ) {
               ?>
               <p class="lacrm-wrapper">
                  <input type="text" name="task-assign-to[]"  id="task-assign-to"
                         placeholder="<?php _e( 'LACRM user ID', 'lacrmconnector' ); ?>"
                         value="<?php echo esc_attr( $value[ 'assigned_to' ] ); ?>" />
                  <input type="text" name="task-due-date[]" id="task-due-date"
                         placeholder="<?php _e( 'Due (today + X days)', 'lacrmconnector' ); ?>" 
                         value="<?php echo esc_attr( $value[ 'due_date' ] ); ?>" />
                  <input type="text" name="task-description[]" id="task-description" 
                         placeholder="<?php _e( 'Task description', 'lacrmconnector' ); ?>" 
                         value="<?php echo esc_attr( $value[ 'description' ] ); ?>" />
                  <span class="icon-remove remove-task-fields">
                     <img src="<?php echo LACRM_CONNECTOR_URL ?>/assets/img/trash.png" title="remove" />
                  </span>            
               </p>
               <?php
            }
         }
         ?>
         <br class="clearfix">
         <!-- add new note -->
         <input type="button" name="add-task-fields" id="add-task-fields" value="<?php _e( 'Add Task', 'lacrmconnector' ); ?>"
                class="button button-secondary add-task-fields" />
      </div>
   </div>
   <!-- End of Adding task -->

   <input type="hidden" value="<?php echo $form_id; ?>" id="form-id"/>

   <!-- set nonce -->
   <input type="hidden" name="lacrm-ajax-nonce" id="lacrm-ajax-nonce" value="<?php echo wp_create_nonce( 'lacrm-ajax-nonce' ); ?>" />
   <span class="loading-save">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

</div>
