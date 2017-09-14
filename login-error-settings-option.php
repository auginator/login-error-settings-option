<?php
/**
 * Plugin Name: Login Error Settings Option
 * Description: Shh! Wordpress, keep quiet! Single custom login screen error message.
 * Version: 1.0.0
 * Author: Agustin Sevilla
 * Author URI: agustinsevilla.com
 * License: GPL2
 */
 
add_action('admin_menu', 'lesp_submenu');

function lesp_submenu() {
    add_submenu_page( 'options-general.php', 'Login Error Message', 'Login Error Message', 'administrator', 'lesp_submenu-settings', 'lesp_submenu_settings_page' );
}
add_action( 'admin_init', 'lesp_my_plugin_settings' );

function lesp_my_plugin_settings() {
    register_setting( 'lesp-settings-group', 'lesp_custom_error' );
    register_setting( 'lesp-settings-group', 'lesp_custom_pw_reset_message' );
    register_setting( 'lesp-settings-group', 'lesp_custom_forgot_pw_confirm' );
}

function lesp_submenu_settings_page() {
   ?>
<div class="wrap">

    <h2>Custom Login Error Message</h2>

    <p>Use this setting to override the overly helpful login error messages with your own.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'lesp-settings-group' ); ?>
        <?php do_settings_sections( 'lesp-settings-group' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="lesp_custom_error">Custom Error Message</label>
                </th>
                <td><input name="lesp_custom_error" type="text" id="lesp_custom_error" value="<?php echo esc_attr( get_option('lesp_custom_error') ); ?>" class="regular-text" /></td>
            </tr>        
            <tr>
                <th scope="row">
                    <label for="lesp_custom_pw_reset_message">Password reset message.</label>
                </th>
                <td><input name="lesp_custom_pw_reset_message" type="text" id="lesp_custom_pw_reset_message" value="<?php echo esc_attr( get_option('lesp_custom_pw_reset_message') ); ?>" class="regular-text" /></td>
            </tr>        
            <tr>
                <th scope="row">
                    <label for="lesp_custom_forgot_pw_confirm">Forgot email confirmation message.</label>
                </th>
                <td><input name="lesp_custom_forgot_pw_confirm" type="text" id="lesp_custom_forgot_pw_confirm" value="<?php echo esc_attr( get_option('lesp_custom_forgot_pw_confirm') ); ?>" class="regular-text" /></td>
            </tr>        
        </table>
        
        <?php submit_button(); ?>

    </form>
</div>
<?php 
}

// Filters to control messaging on the forms in the login
add_filter('login_message', 'lesp_login_message'); // Edit the login messages
add_filter('login_errors','lesp_login_error_message'); // Edit the error messages in the login (e.g.)


/**
* lesp_login_message() is used to filter the login_message text for normal (non-error) messages.
* @param string $message This is the wp generated message we may want to change 
*/
function lesp_login_message( $message ) {

    $action = $_REQUEST['action'];

    switch ($action) {
        case 'lostpassword':

            if($_REQUEST['user_login']) { // If the person has already submitted the form…

                // We do not want to divulge whether or not the email exists.
                // Show the Message that the user has configured, instead of the normal one.
                // lesp_login_error_message() will remove the error associated with bad email entry, 
                // so it will no longer be possible to tell whether the email exists or not.
                $message = '<p class="message">' . get_option('lesp_custom_forgot_pw_confirm') . '</p>';

            } else {

                $message = '<p class="message">' . get_option('lesp_custom_pw_reset_message') . '</p>';
            }
            
            break; 

        case 'resetpass':
            # code...
            
            break;

        case 'login':
            # code...
            break;
        
        default:
            # code...
            break;
    }
    return $message;
}

/**/

function lesp_login_error_message( $error ){

    global $errors;
    $error_messages_to_suppress = [
        'invalid_email', 
        'invalid_username', 
        'incorrect_password', 
        'invalidcombo', //invalidcombo triggers on invalid email structure 
        'checkemail'
        ]; 
    $errors_suppressed_count = 0;
    $suppress_error_message = false;

    //For debugging
    // $err = json_encode( $errors->get_error_codes() ); $req = json_encode( $_REQUEST ); $action = $_REQUEST['action'];
    // echo('<script>console.log('.$req.'); console.log('.$err.'); console.log('.$action.');</script>');

    // Finally, remove any errors that we do not want the user to see.
    // NOTE: unset($errors); This does not seem to make WP stop outputting the error object altogether. 
    $err_codes = $errors->get_error_codes();
    
    foreach ( $error_messages_to_suppress as $e ) {
        if( in_array( $e, $err_codes ) ) {

            $errors->remove( $e );
            // Suppress the error message related to this state.
            $suppress_error_message = true;
            $errors_suppressed_count++;
        }
    } 

    //If we have some errors, just give the user configured message.
    if ( $suppress_error_message && get_option( 'lesp_custom_error' ) && $_REQUEST['action']!='lostpassword' ) {

        $error = get_option( 'lesp_custom_error' );
        return $error;

    }

    // Check to see what the number of errors is now.
    if( count( $err_codes ) - $errors_suppressed_count <= 0 ) {
        echo '<style>#login #login_error:empty { display: none; }</style>';
    }
}
/**
* Remove the login shake.
* Too good of a clue…
*/
// function lesp_login_head() {
// remove_action('login_head', 'wp_shake_js', 12);
// }
// add_action('login_head', 'lesp_login_head');
/**
* Remove Login Shake with filter
*/
add_filter( 'shake_error_codes', lesp_remove_shake_errors);
function lesp_remove_shake_errors($shake_error_codes) {
    //We want to remove this completely.
    return [];
}
