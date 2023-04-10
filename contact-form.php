<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<?php
/*
Plugin Name: Contact-Form
Description: This contact form adds to your WordPress website a contact form just via typing a Short Code by soufian.
Version: 1.0
Author: Soufian Tamim
Author URI: https://soufiantamim.com
License: SSL
*/
function now() {
    return date('Y-m-d H:i:s');
}

// ===================================== CONTACT FORM ACTIVATE
function contact_form_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'contact_form';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `subject` varchar(100) NOT NULL,
        `first_name` varchar(50) NOT NULL,
        `last_name` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `message` text NOT NULL,
        `date_submitted` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'contact_form_install' );

// ===================================== CONTACT FORM DEACTIVATE

function contact_form_deactivation() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'contact_form_deactivation');

// ===================================== CONTACT FORM ADD SHORTCODE

function contact_form_shortcode() {
  $output = '<form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';

  $output .= '<div class="form-group">';
  $output .= '<label for="subject" class="text-uppercase">Subject:</label>';
  $output .= '<input type="text" class="form-control" name="subject" required>';
  $output .= '</div>';

  $output .= '<div class="form-group">';
  $output .= '<label for="first_name" class="text-uppercase">First Name:</label>';
  $output .= '<input type="text" class="form-control" name="first_name" required>';
  $output .= '</div>';

  $output .= '<div class="form-group">';
  $output .= '<label for="last_name" class="text-uppercase">Last Name:</label>';
  $output .= '<input type="text" class="form-control" name="last_name" required>';
  $output .= '</div>';

  $output .= '<div class="form-group">';
  $output .= '<label for="email" class="text-uppercase">Email:</label>';
  $output .= '<input type="email" class="form-control" name="email" required>';
  $output .= '</div>';

  $output .= '<div class="form-group">';
  $output .= '<label for="message" class="text-uppercase">Message:</label>';
  $output .= '<textarea class="form-control" name="message" required></textarea>';
  $output .= '</div>';

  $output .= '<button type="submit" class="btn btn-primary mt-4" name="submit">Submit</button>';

  $output .= '</form>';
  return $output;
}

add_shortcode( 'contact_form', 'contact_form_shortcode' );

// ===================================== PROCESS FORM SUBMISSION

function process_contact_form() {
        if(isset($_POST['submit'])) {
          global $wpdb;
          $table_name = $wpdb->prefix . 'contact_form';

          $subject = $_POST['subject'];
          $first_name = $_POST['first_name'];
          $last_name = $_POST['last_name'];
          $email = $_POST['email'];
          $message = $_POST['message'];

          $data = array(
          'subject' => $subject,
          'first_name' => $first_name,
          'last_name' => $last_name,
          'email' => $email,
          'message' => $message,
          'date_submitted' => now()
          );
          
          $format = array('%s', '%s', '%s', '%s', '%s', '%s');
          
          $wpdb->insert($table_name, $data, $format);

          if($wpdb->insert_id > 0) {
            echo '<div class="alert alert-success d-flex align-items-center" role="alert"><p>Your message has been sent.</p></div>';
            } else {
            echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><p>An error occurred while sending your message.</p></div>';
          }
  } 
}

add_action('init', 'process_contact_form');

function contact_form_admin_menu() {
  add_menu_page(
      'Contact Form',     
      'Contact Form',     
      'manage_options',
      'contact-form',
      'contact_form_page',
      'https://cdn-icons-png.flaticon.com/16/2991/2991110.png',   
  );
}
add_action( 'admin_menu', 'contact_form_admin_menu' );

function contact_form_page() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'contact_form';
  $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_submitted DESC");

  echo '<div class="wrap">';
  echo '<h1>Contact Form Messages</h1>';
  echo '<table class="wp-list-table widefat fixed striped">';
  echo '<thead><tr><th>Subject</th><th>Name</th><th>Email</th><th>message</th><th>Date Submitted</th></tr></thead>';
  echo '<tbody>';
  foreach ($messages as $message) {
    echo '<tr>';
    echo '<td>' . $message->subject . '</td>';
    echo '<td>' . $message->first_name . ' ' . $message->last_name . '</td>';
    echo '<td>' . $message->email . '</td>';
    echo '<td>' . $message->message . '</td>';
    echo '<td>' . $message->date_submitted . '</td>';
    echo '</tr>';
  }
  echo '</tbody></table>';
  echo '</div>';
}