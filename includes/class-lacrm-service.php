<?php

/**
 * Service class for CF7 LACRM Connector
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

/**
 * Lacrm_Connector_Service Class
 *
 * @since 1.0
 */
class Lacrm_Connector_Service {

   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      // Add new tab to contact form 7 editors panel
      add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_lacrm_editor_panels' ) );

      add_action( 'wp_ajax_verify_integation', array( $this, 'verify_integation' ) );
      add_action( 'wp_ajax_save_lacrm_info', array( $this, 'save_lacrm_info' ) );
      add_action( 'wp_ajax_verify_form', array( $this, 'verify_form' ) );
      // tiggers when we submit the contact form
      add_action( 'wpcf7_mail_sent', array( $this, 'your_wpcf7_lacrm_sent_function' ) );
   }

   /**
    * Add new tab to contact form 7 editors panel
    * @since 1.0
    */
   public function cf7_lacrm_editor_panels( $panels ) {

      $panels[ 'lacrm' ] = array(
          'title' => __( 'LACRM', 'contact-form-7' ),
          'callback' => array( $this, 'wpcf7_editor_panel_lacrm' )
      );
      return $panels;
   }

   /**
    * AJAX function - verifies the token
    *
    * @since 1.0
    */
   public function verify_integation() {
      // nonce check
      check_ajax_referer( 'lacrm-integation-nonce', 'security' );

      /* sanitize incoming data */
      $UserCode = sanitize_text_field( $_POST[ "user_code" ] );
      $APIToken = sanitize_text_field( $_POST[ "api_token" ] );
      $default_id = intval( $_POST[ "default_id" ] );

      $EndpointURL = "https://api.lessannoyingcrm.com";
      $Function = "CreateContact";
      $default_value = "wcf7-" . $default_id;
      $Parameters = array( "FullName" => $default_value );

      $APIResult = file_get_contents( "$EndpointURL?UserCode=$UserCode&APIToken=$APIToken&" .
              "Function=$Function&Parameters=" . urlencode( json_encode( $Parameters ) ) );
      $APIResult = json_decode( $APIResult, true );
      if ( @$APIResult[ 'Success' ] === true ) {
         update_option( "lacrm_user_code", $UserCode );
         update_option( "lacrm_api_token", $APIToken );
         update_option( "lacrm_verify_token", 'valid' );
         wp_send_json_success();
      } else {
         update_option( "lacrm_verify_token", 'invalid' );
         wp_send_json_error();
      }

      return $APIResult;
   }

   /**
    * AJAX function - verifies the token
    *
    * @since 1.0
    */
   public function save_lacrm_info() {
      global $wpdb;
      $task = array();
      // nonce check
      check_ajax_referer( 'lacrm-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $form_id = intval( sanitize_text_field( $_POST[ "form_id" ] ) );

      $contact_info = isset( $_POST[ "contact_info" ] ) ? sanitize_text_field( $_POST[ "contact_info" ] ) : "";
      $custom_info = isset( $_POST[ "custom_info" ] ) ? sanitize_text_field( $_POST[ "custom_info" ] ) : "";

      $pipeline_info = isset( $_POST[ "pipeline_info" ] ) ? sanitize_text_field( $_POST[ "pipeline_info" ] ) : "";
      // we want notes to be displayed as user input to it so do not sanitize
      $notes = $_POST[ "notes" ];
      $pipeline_fields = isset( $_POST[ "pipeline_fields" ] ) ? sanitize_text_field( $_POST[ "pipeline_fields" ] ) : "";

      $assigned_to = isset( $_POST[ "assigned_to" ] ) ? array_map( 'esc_attr', $_POST[ "assigned_to" ] ) : "";
      $due_date = isset( $_POST[ "due_date" ] ) ? array_map( 'esc_attr', $_POST[ "due_date" ] ) : "";
      $description = isset( $_POST[ "description" ] ) ? array_map( 'esc_attr', $_POST[ "description" ] ) : "";

      // to prevent the offset warnings
      foreach ( $assigned_to as $key => $val ) {
         if ( empty( $assigned_to[ $key ] ) ) {
            unset( $assigned_to[ $key ] );
            unset( $due_date[ $key ] );
            unset( $description[ $key ] );
         }
      }
      foreach ( $due_date as $key => $val ) {
         if ( empty( $due_date[ $key ] ) ) {
            unset( $assigned_to[ $key ] );
            unset( $due_date[ $key ] );
            unset( $description[ $key ] );
         }
      }
      foreach ( $description as $key => $val ) {
         if ( empty( $description[ $key ] ) ) {
            unset( $assigned_to[ $key ] );
            unset( $due_date[ $key ] );
            unset( $description[ $key ] );
         }
      }

      if ( count( $assigned_to ) > 0 ) {
         for ( $index = 1; $index <= count( $assigned_to ); $index ++ ) {
            $data = array(
                'assigned_to' => $assigned_to[ $index ],
                'due_date' => $due_date[ $index ],
                'description' => $description[ $index ]
            );
            array_push( $task, $data );
         }
      }

      $table_name = Lacrm_Connector_Utility::instance()->get_lacrm_table_name();
      $existing_result = $this->get_form_data( $form_id );
      if ( $existing_result ) { // here we are updating existing form data
         $result = $wpdb->update(
                 $table_name, array(
             'contact_info' => stripcslashes( $contact_info ),
             'custom_fields' => stripcslashes( $custom_info ),
             'pipeline_info' => stripcslashes( $pipeline_info ),
             'notes' => $notes,
             'pipeline_fields' => stripcslashes( $pipeline_fields ),
             'tasks' => stripcslashes( serialize( $task ) ),
                 ), array( 'form_id' => $form_id, )
         );
      } else { // insert new form data
         $result = $wpdb->insert(
                 $table_name, array(
             'form_id' => $form_id,
             'contact_info' => stripcslashes( $contact_info ),
             'custom_fields' => stripcslashes( $custom_info ),
             'pipeline_info' => stripcslashes( $pipeline_info ),
             'notes' => $notes,
             'pipeline_fields' => stripcslashes( $pipeline_fields ),
             'tasks' => stripcslashes( serialize( $task ) ),
                 )
         );
      }
      wp_send_json_success( admin_url() );
   }

   /**
    * AJAX function - verifies whether the form data exist for lacrm connector
    * @since 1.0
    */
   public function verify_form() {
      global $wpdb;
      $ids = array();
      // nonce check
      check_ajax_referer( 'lacrm-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $form_id = intval( sanitize_text_field( $_POST[ "form_id" ] ) );
      $table_name = Lacrm_Connector_Utility::instance()->get_lacrm_table_name();
      $result = $wpdb->get_results( " SELECT form_id as form_ids FROM " . $table_name . " " );
      if ( $result ) {
         foreach ( $result as $form_ids ) {
            $form_ids = $form_ids->form_ids;
            $ids[] = $form_ids;
         }
      }
      if ( in_array( $form_id, $ids ) ) {
         wp_send_json_success( admin_url() );
      } else {
         wp_send_json_error();
      }
   }

   /*
    * Hook - Send data to LACRM 
    * @since 1.0
    */

   public function your_wpcf7_lacrm_sent_function( $form ) {

      $submission = WPCF7_Submission::get_instance();

      $url = $submission->get_meta( 'url' );
      $remote_ip = $submission->get_meta( 'remote_ip' );
      $EndpointURL = "https://api.lessannoyingcrm.com";

      //Get city from which form has been submitted.
      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_URL, 'http://api.ipinfodb.com/v3/ip-city/?key=a5e347151cb058d2bc89de278e6ffec5bedaf6e093d6d7b5b0ca9a0be66b889a&ip=' .
              urlencode( $remote_ip ) );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      $Loc = curl_exec( $ch );


      $args = array();
      $args = wp_parse_args( $args, array(
          'html' => false,
          'exclude_blank' => false ) );

      // get form data
      $form_id = $form->id();
      $form_data = $this->get_form_data( $form_id );

      // -----------set data to send it to LACRM ------------ //

      $UserCode = get_option( 'lacrm_user_code' );
      $APIToken = get_option( 'lacrm_api_token' );

      //------------ contact information---------------
      $contact_info = json_decode( $form_data->contact_info );
      $custom_info = json_decode( $form_data->custom_fields );

      $FullName = $this->lacrm_replace_tags( $contact_info->FullName, $args );

      $Email = array(
          0 => array(
              "Text" => $this->lacrm_replace_tags( $contact_info->Email, $args ),
              "Type" => "Personal"
          )
      );


      $Phone = array(
          0 => array(
              "Text" => $this->lacrm_replace_tags( $contact_info->Phone, $args ),
              "Type" => "Mobile"
          )
      );

      $CompanyName = $this->lacrm_replace_tags( $contact_info->CompanyName, $args );

      $BasicCustomFields = array();
      foreach ( $custom_info as $custom_id => $custom_value ) {
         if ( stripos( $custom_value, 'checkbox' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'radio' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'text' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'textarea' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'number' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'menu' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $BasicCustomFields[ $custom_id ] = $data;
         }
         if ( stripos( $custom_value, 'date' ) !== false ) {
            $data = $this->lacrm_replace_tags( $custom_value, $args );
            $originalDate = $data;
            $newDate = date( "m-d-Y", strtotime( $originalDate ) );
            $BasicCustomFields[ $custom_id ] = $newDate;
         }
      }

      // function for creating new contact and save to lacrm
      $Function = "CreateContact";

      // set lacrm parameters
      $Parameters = array(
          "FullName" => $FullName,
          "Email" => $Email,
          "Phone" => $Phone,
          "CompanyName" => $CompanyName,
      );
      $Parameters[ 'CustomFields' ] = $BasicCustomFields;

      //The CallAPI function is at the bottom of this file
      // Send the new contact information to larcm
      $Result = $this->CallAPI( $EndpointURL, $UserCode, $APIToken, $Function, $Parameters );

      //Get the new ContactId
      $ContactId = $Result[ 'ContactId' ];

      //------------ Pipeline information----------
      $pipeline_info = json_decode( $form_data->pipeline_info );
      $notes_info = $form_data->notes;
      $pipeline_fields = json_decode( $form_data->pipeline_fields );

      // create pipeline function   
      $Function = "CreatePipeline";

      $pipeline_id = $pipeline_info->PipelineId;
      $StatusId = $pipeline_info->StatusId;
      $priority = $pipeline_info->Priority;

      //Starting with note
      $TheNote = "\n";
      $TheNote .= $this->lacrm_replace_tags( $notes_info, $args );
      $TheNote .= "\n";
      $TheNote .= "URL: ";
      $TheNote .= $url;
      $TheNote .= "\n";
      $TheNote .= "Remote IP: ";
      $TheNote .= $remote_ip;
      $TheNote .= "\n";
      $TheNote .= $Loc;

      // set pipeline parameters
      $Parameters = array(
          "ContactId" => $ContactId,
          "Note" => $TheNote,
          "PipelineId" => $pipeline_id,
          "StatusId" => $StatusId,
          "Priority" => $priority
      );

      $pipeline_checkbox = array();
      $CustomFields = array();

      foreach ( $pipeline_fields as $pipe_id => $pipe_value ) {
         if ( stripos( $pipe_value, 'checkbox' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            if ( ! empty( $data ) ) {
               $checkbox_data = explode( ',', $data );
               foreach ( $checkbox_data as $value ) {
                  $pipeline_checkbox[ $value ] = "on";
               }
               $CustomFields[ $pipe_id ] = $pipeline_checkbox;
               unset( $pipeline_checkbox );
            }
         }
         if ( stripos( $pipe_value, 'radio' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
         if ( stripos( $pipe_value, 'text' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
         if ( stripos( $pipe_value, 'textarea' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
         if ( stripos( $pipe_value, 'number' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
         if ( stripos( $pipe_value, 'menu' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
         if ( stripos( $pipe_value, 'date' ) !== false ) {
            $data = $this->lacrm_replace_tags( $pipe_value, $args );
            $CustomFields[ $pipe_id ] = $data;
         }
      }


      $Parameters[ 'CustomFields' ] = $CustomFields;

      //This call returns a "PipelineItemId" which is a specific instance of the pipeline
      $Result = $this->CallAPI( $EndpointURL, $UserCode, $APIToken, $Function, $Parameters );


      //-----------Task Information-------------
      $task = unserialize( $form_data->tasks );
      $FunctionTask = "CreateTask";

      foreach ( $task as $value ) {
         $date = $value[ 'due_date' ];
         $due_date = date( "Y-m-d", time() + ( $date * 24 * 60 * 60 ) );
         $description = $value[ 'description' ];
         $assigned_to = $value[ 'assigned_to' ];
         $ParametersTask = array(
             "DueDate" => $due_date, //This date should be formated YYYY-MM-DD
             "Description" => $description, //Required, the name of the task
             "ContactId" => $ContactId, //Optional
             "AssignedTo" => $assigned_to
         );
         $Result = $this->CallAPI_Task( $EndpointURL, $UserCode, $APIToken, $FunctionTask, $ParametersTask );
      }
   }

   /*
     The API doesn't look at the type of request (POST, GET, etc) so you can use whatever
     you want. Here is a really ugly way to handle this in PHP:
    */

   public function CallAPI( $EndpointURL, $UserCode, $APIToken, $Function, $Parameters ) {
      $APIResult = file_get_contents( "$EndpointURL?UserCode=$UserCode&APIToken=$APIToken&" .
              "Function=$Function&Parameters=" . urlencode( json_encode( $Parameters ) ) );
      $APIResult = json_decode( $APIResult, true );
      return $APIResult;
   }

   public function CallAPI_Task( $EndpointURL, $UserCode, $APIToken, $FunctionTask, $ParametersTask ) {
      $APIResult = file_get_contents( "$EndpointURL?UserCode=$UserCode&APIToken=$APIToken&" .
              "Function=$FunctionTask&Parameters=" . urlencode( json_encode( $ParametersTask ) ) );
      $APIResult = json_decode( $APIResult, true );
      return $APIResult;
   }

   /*
    * Display Lacrm Settings Page 
    * @since 1.0
    */

   function wpcf7_editor_panel_lacrm( $post ) {
      include( LACRM_CONNECTOR_PATH . "includes/pages/lacrm.php" );
   }

   /*
    * Fetch form names to display on lacrm admin page
    * @since 1.0
    */

   public function fetch_form_name() {
      global $wpdb;
      $from_names = $wpdb->get_results( " SELECT posts.ID as form_id, posts.post_title as form_name FROM $wpdb->posts as posts WHERE post_type='wpcf7_contact_form' ORDER BY posts.ID " );
      return $from_names;
   }

   /*
    * Fetch the form data to display on LACRM page and to send data to LACRM 
    * @since 1.0
    */

   public function get_form_data( $form_id ) {
      global $wpdb;
      // sanitize the data 
      $form_id = intval( $form_id );
      $table_name = Lacrm_Connector_Utility::instance()->get_lacrm_table_name();
      $result = $wpdb->get_row( " SELECT * FROM " . $table_name . " WHERE form_id = ' " . $form_id . " ' " );
      return $result;
   }

   function lacrm_replace_tags( $content, $args ) {

      if ( is_array( $content ) ) {
         foreach ( $content as $key => $value ) {
            $content[ $key ] = wpcf7_mail_replace_tags( $value, $args );
         }

         return $content;
      }

      $content = explode( "\n", $content );

      foreach ( $content as $num => $line ) {
         $line = new WPCF7_MailTaggedText( $line, $args );
         $replaced = $line->replace_tags();

         if ( $args[ 'exclude_blank' ] ) {
            $replaced_tags = $line->get_replaced_tags();

            if ( empty( $replaced_tags ) || array_filter( $replaced_tags ) ) {
               $content[ $num ] = $replaced;
            } else {
               unset( $content[ $num ] ); // Remove a line.
            }
         } else {
            $content[ $num ] = $replaced;
         }
      }

      $content = implode( "\n", $content );

      return $content;
   }

}

// construct an instance so that the actions get loaded
$lacrm_connector_service = new Lacrm_Connector_Service();


class WPCF7_MailTagText {

   private $html = false;
   private $callback = null;
   private $content = '';
   private $replaced_tags = array();

   public function __construct( $content, $args = '' ) {
      $args = wp_parse_args( $args, array(
          'html' => false,
          'callback' => null ) );

      $this->html = (bool) $args[ 'html' ];

      if ( null !== $args[ 'callback' ] && is_callable( $args[ 'callback' ] ) ) {
         $this->callback = $args[ 'callback' ];
      } elseif ( $this->html ) {
         $this->callback = array( $this, 'replace_tags_callback_html' );
      } else {
         $this->callback = array( $this, 'replace_tags_callback' );
      }

      $this->content = $content;
   }

   public function get_replaced_tags() {
      return $this->replaced_tags;
   }

   public function replace_tags() {
      $regex = '/(\[?)\[[\t ]*'
              . '([a-zA-Z_][0-9a-zA-Z:._-]*)' // [2] = name
              . '((?:[\t ]+"[^"]*"|[\t ]+\'[^\']*\')*)' // [3] = values
              . '[\t ]*\](\]?)/';

      return preg_replace_callback( $regex, $this->callback, $this->content );
   }

   private function replace_tags_callback_html( $matches ) {
      return $this->replace_tags_callback( $matches, true );
   }

   private function replace_tags_callback( $matches, $html = false ) {
      // allow [[foo]] syntax for escaping a tag
      if ( $matches[ 1 ] == '[' && $matches[ 4 ] == ']' ) {
         return substr( $matches[ 0 ], 1, -1 );
      }

      $tag = $matches[ 0 ];
      $tagname = $matches[ 2 ];
      $values = $matches[ 3 ];

      if ( ! empty( $values ) ) {
         preg_match_all( '/"[^"]*"|\'[^\']*\'/', $values, $matches );
         $values = wpcf7_strip_quote_deep( $matches[ 0 ] );
      }

      $do_not_heat = false;

      if ( preg_match( '/^_raw_(.+)$/', $tagname, $matches ) ) {
         $tagname = trim( $matches[ 1 ] );
         $do_not_heat = true;
      }

      $format = '';

      if ( preg_match( '/^_format_(.+)$/', $tagname, $matches ) ) {
         $tagname = trim( $matches[ 1 ] );
         $format = $values[ 0 ];
      }

      $submission = WPCF7_Submission::get_instance();
      $submitted = $submission ? $submission->get_posted_data( $tagname ) : null;

      if ( null !== $submitted ) {

         if ( $do_not_heat ) {
            $submitted = isset( $_POST[ $tagname ] ) ? $_POST[ $tagname ] : '';
         }

         $replaced = $submitted;

         if ( ! empty( $format ) ) {
            $replaced = $this->format( $replaced, $format );
         }

         $replaced = wpcf7_flat_join( $replaced );

         if ( $html ) {
            $replaced = esc_html( $replaced );
            $replaced = wptexturize( $replaced );
         }

         $replaced = apply_filters( 'wpcf7_mail_tag_replaced', $replaced, $submitted, $html );

         $replaced = wp_unslash( trim( $replaced ) );

         $this->replaced_tags[ $tag ] = $replaced;
         return $replaced;
      }

      $special = apply_filters( 'wpcf7_special_mail_tags', '', $tagname, $html );

      if ( ! empty( $special ) ) {
         $this->replaced_tags[ $tag ] = $special;
         return $special;
      }

      return $tag;
   }

   public function format( $original, $format ) {
      $original = (array) $original;

      foreach ( $original as $key => $value ) {
         if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value ) ) {
            $original[ $key ] = mysql2date( $format, $value );
         }
      }

      return $original;
   }

}
