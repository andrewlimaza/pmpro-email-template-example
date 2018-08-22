<?php
/*
Plugin Name: Paid Memberships Pro - Email Template Example
Plugin URI: https://www.paidmembershipspro.com/add-ons/email-templates-admin-editor/
Description: Example of adding a custom email template to Paid Memberships Pro
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
Version: 0.1
*/

/**
 * Add a Member Link to the Membership Account page to
 * send our email to the current user.
 */
function example_pmpro_member_links_top() {
	$url = add_query_arg( 'sendexampleemail', 1, pmpro_url('account') );
	$url = wp_nonce_url( $url, 'sendexampleemail' );
	?>
	<li><a target="_blank" href="<?php echo esc_url( $url );?>">Send me the example email.</a></li>
	<?php
}
add_filter( 'pmpro_member_links_top','example_pmpro_member_links_top' );

/**
 * Send the example email when the link is clicked.
 * We hook on init and check for the URL parameters.
 */
function example_init_send_example_email() {
	global $current_user;

	if( !empty( $_REQUEST['sendexampleemail'] ) && !empty( $_REQUEST['_wpnonce'] ) ) {
		// Check the nonce.
		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sendexampleemail' ) ) {
			die( 'Invalid nonce. Try again.' );
		}

		// Check for a current user and email address
		if(empty($current_user->user_email)) {
			die( 'Must be logged in with an email address on record.' );
		}

		// Setup the new email.
		$pmpro_email = new PMProEmail();

		// Setup the email data
		$pmpro_email->body = file_get_contents( dirname( __FILE__ ) . "/email/example.html" );;
		$pmpro_email->subject = 'Example Email Template Subject Line for !!sitename!!';
		$pmpro_email->email = $current_user->user_email;
		$pmpro_email->data = array( 
			"display_name" => $current_user->display_name,
			"user_email" => $current_user->user_email,
			"login_link" => wp_login_url(),
			"membership_level_name" => $current_user->membership_level->name,
		);
		$pmpro_email->template = 'example';
		$pmpro_email->sendEmail();

		// Success Message
		die( 'Email sent. You can close this window.' );
	}
}
add_action( 'init', 'example_init_send_example_email', 20 );

/**
 * Tell PMPro to use the ../email/example.html file with this plugin.
 * Newer versions of PMPro will use the pmpro_loadTemplate function to load
 * email templates, and at that time, this filter will add our template to
 * the list to be checked. Until then, we manually swap the body in our code
 * above.
 */
function example_add_email_template( $templates, $page_name, $type = 'emails', $where = 'local', $ext = 'html' ) {
	$templates[] = dirname(__FILE__) . "/email/example.html";

	return $templates;
}
add_filter( 'pmpro_email_custom_template_path', 'example_add_email_template', 10, 5 );

/**
 * Integrate with Email Templates Admin Editor
 * Add our template to the list of templates to edit.
 */
function example_email_templates( $templates ) {

	// Add the resend email confirmation template.
	$templates['example'] = array(
		'subject' => 'Example Email Template Subject Line for !!sitename!!',
		'description' => 'Example Email Template',
		'body' => file_get_contents( dirname( __FILE__ ) . "/email/example.html" )
	);

	return $templates;

}
add_filter( 'pmproet_templates', 'example_email_templates', 10, 1 );