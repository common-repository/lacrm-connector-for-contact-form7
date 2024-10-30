jQuery(document).ready(function () {
    
   /**
    * verify the api code
    * @since 1.0
    */
    jQuery(document).on('click', '.verify-token', function () {
        jQuery( ".loading-sign" ).addClass( "loading" );
        var data = {
        action: 'verify_integation',
        user_code: jQuery('#user-code-text').val(),
        api_token: jQuery('#token-text').val(),
        default_id: jQuery('#default-id').val(),
        security: jQuery( '#lacrm-integation-nonce' ).val()
      };
      jQuery.post(ajaxurl, data, function (response) {
          if( ! response.success ) { 
            jQuery( ".loading-sign" ).removeClass( "loading" );
            jQuery( "#validation-message" ).empty();
            jQuery("<span class='invalid-message'>Invalid User Code or API Token</span>").appendTo('#validation-message');
          }
          else {
            jQuery( ".loading-sign" ).removeClass( "loading" );
            jQuery( "#validation-message" ).empty();
            jQuery("<span class='valid-message'> Valid User Code and API Token</span>").appendTo('#validation-message');  
          }
      });
    
    });
    
   /**
    * Add new custom field
    * @since 1.0
    */
    jQuery(document).on('click', '.add-new-field', function (event) {
      event.preventDefault();

      // step 1. get the parent of button ie <div>
      var parent = jQuery(this).parent();
      
      // <div>'s first child always be hidden so clone it.
      var copy_wrapper = parent.children(':first')
              .clone()
              .removeClass('lacrm-wrapper-hidden')
              .addClass('lacrm-wrapper')
              .fadeIn('slow', function () {
                 jQuery(this).delay(800);
              });

      // now append the data before the add custom field button
      jQuery(copy_wrapper).insertBefore(parent.children(':last'));
   });
   
   /**
    * Remove custom field when trash icon is clicked
    * @since 1.0
    */
   jQuery(document).on('click', '.remove-cutom-field', function (event) {
      event.preventDefault();

      // get parent of clicked icon ie div of the condition
      var parent = jQuery(this).parent();

      parent.addClass('remove-cutom-field').fadeOut(1000, function () {
         jQuery(this).remove();
      });
   }); 
   
   /**
    * Save Lacrm Form
    * @since 1.0
    */
    jQuery(document).on('submit', '#wpcf7-admin-form-element', function () {
       var saveformdata = new Array();
       var saveformdata = get_step_data();
       
       var form_id = jQuery('#form-id').val();
       
       // task information
       var assigned_to = jQuery("input[id='task-assign-to']").map(function(){return jQuery(this).val();}).get() ;
       var due_date = jQuery("input[id='task-due-date']").map(function(){return jQuery(this).val();}).get();
       var description = jQuery("input[id='task-description']").map(function(){return jQuery(this).val();}).get();
       
      jQuery( ".loading-save" ).addClass( "loading" );
       
       data = {
            action: 'save_lacrm_info',
            form_id:form_id,
            contact_info: saveformdata[0],
            custom_info: saveformdata[1],
            pipeline_info: saveformdata[2],
            notes: jQuery('#note-message').val(),
            pipeline_fields: saveformdata[3],
            assigned_to:assigned_to,
            due_date:due_date,
            description:description,
            security: jQuery( '#lacrm-ajax-nonce' ).val()
        };
        
       jQuery.post(ajaxurl, data, function (response) {
         admin_url = response.data;
         if( response.success ) {
            jQuery( ".loading-save" ).removeClass( "loading" );
            window.location.href = admin_url + 'admin.php?page=wpcf7&post='+ form_id + '&action=edit';
         }
      });  
       
    });
    
   var get_step_data = function () {
      var contact_info_array = {};
      var custom_info_array = {};
      var pipeline_info_array = {};
      var pipeline_fields_array = {};
      
      //extract contact info
      contact_info_array["FullName"] = jQuery("#basic-field-name").val();
      contact_info_array["Email"] = jQuery("#basic-field-email").val();
      contact_info_array["Phone"] = jQuery("#basic-field-phone").val();
      contact_info_array["CompanyName"] = jQuery("#basic-field-company").val();
      
      // extract custom fields
      var custom_field_name = jQuery("input[id='field-name']").map(function(){return jQuery(this).val();}).get();
      var custom_field_value = jQuery("input[id='field-value']").map(function(){return jQuery(this).val();}).get();
      var l = Math.min(custom_field_name.length,custom_field_value.length), i;
      for( i=1; i<l; i++) {
         custom_info_array[custom_field_name[i]] = custom_field_value[i]; 
      }
      
      // extract pipeline info
      pipeline_info_array["PipelineId"] = jQuery("#pipeline-id").val();
      pipeline_info_array["StatusId"] = jQuery("#pipeline-status").val();
      pipeline_info_array["Priority"] = jQuery("#pipeline-priority").val();
      
      
      // extact custom pipeline fields
      var custom_pipeline_id = jQuery("input[id='custom-pipeline-id']").map(function(){return jQuery(this).val();}).get() ;
      var custom_pipeline_value = jQuery("input[id='pipeline-value']").map(function(){return jQuery(this).val();}).get();
      var len = Math.min(custom_pipeline_id.length,custom_pipeline_value.length), j;
      for( k=1; k<len; k++) {
         pipeline_fields_array[custom_pipeline_id[k]] = custom_pipeline_value[k]; 
      }
          
      
      contact_info = jQuery.toJSON(contact_info_array);// contact info
      custom_info = jQuery.toJSON(custom_info_array); // custom info array
      pipeline_info = jQuery.toJSON(pipeline_info_array); // pipeline info 
      custom_pipeline_info = jQuery.toJSON(pipeline_fields_array); // custom pipeline array
      
      
      var lacrm_data = new Array( contact_info, custom_info, pipeline_info, custom_pipeline_info );
      return lacrm_data;
   };
   
       
   /**
    * Add custom pipeline fields
    * @since 1.0
    */
    jQuery(document).on('click', '.add-custom-pipeline-fields', function (event) {
      event.preventDefault();

      // step 1. get the parent of button ie <div>
      var parent = jQuery(this).parent();
      
      // <div>'s first child always be hidden so clone it.
      var copy_wrapper = parent.children(':first')
              .clone()
              .removeClass('lacrm-wrapper-hidden')
              .addClass('lacrm-wrapper')
              .fadeIn('slow', function () {
                 jQuery(this).delay(800);
              });

      // now append the data before the add custom field button
      jQuery(copy_wrapper).insertBefore(parent.children(':last'));

   });
   
   /**
    * Remove custom pipeline fields when trash icon is clicked
    * @since 1.0
    */
    jQuery(document).on('click', '.remove-custom-pipeline-fields', function (event) {
      event.preventDefault();

      // get parent of clicked icon ie div of the condition
      var parent = jQuery(this).parent();

      parent.addClass('remove-pipeline-fields').fadeOut(1000, function () {
         jQuery(this).remove();
      });
    });  
    
    /**
    * Add task fields
    * @since 1.0
    */
    jQuery(document).on('click', '.add-task-fields', function (event) {
      event.preventDefault();

      // step 1. get the parent of button ie <div>
      var parent = jQuery(this).parent();
      
      // <div>'s first child always be hidden so clone it.
      var copy_wrapper = parent.children(':first')
              .clone()
              .removeClass('lacrm-wrapper-hidden')
              .addClass('lacrm-wrapper')
              .fadeIn('slow', function () {
                 jQuery(this).delay(800);
              });

      // now append the data before the add custom field button
      jQuery(copy_wrapper).insertBefore(parent.children(':last'));

   });
   
   /**
    * Remove task fields when trash icon is clicked
    * @since 1.0
    */
    jQuery(document).on('click', '.remove-task-fields', function (event) {
      event.preventDefault();

      // get parent of clicked icon ie div of the condition
      var parent = jQuery(this).parent();

      parent.addClass('remove-pipeline-fields').fadeOut(1000, function () {
         jQuery(this).remove();
      });
    }); 
    
   /**
    * on change of form display form details
    */
   jQuery(document).on("change", "#select-form-name", function () {
       var form_id = jQuery(this).val(),
        data = {
          action: 'verify_form',
          form_id: form_id,
          security: jQuery( '#lacrm-ajax-nonce' ).val()
       };
      
        jQuery.post(ajaxurl, data, function (response) {
          var admin_url = response.data;
          var link = admin_url + 'admin.php?page=wpcf7-lacrm&formid='+form_id;
          if( response.success ) {
            jQuery( ".select-form-name" ).after( "<a href='" + link + "' class='edit-form'>Edit</a>" );
            jQuery( ".select-form" ).after( "<p class='edit-info'>This form already have a LACRM setup . If you want to update any change click Edit</p>" );
          } 
        }); 
       
   });
    
});