<?php

/**
 * Plugin Name: AuctionAlly eBay Embedder
 * Plugin URI: https://auctionally.co.uk/
 * Description: Show your eBay listings and customise their look easily with our online designer.
 * Version: 1.0
 * Author: Roger Davie
 * Author URI: https://profiles.wordpress.org/rogstamp/
 */


// Shortcode maker.

function auctionally_shortcode_func( $atts ){

    // Get the parameters string.
	$auctionally_parameters_code_val = trim(get_option('auctionally_parameters_code'));
  
    // 1. Split the parameter code up and look for the prp name (parameters for the script to pass in the shop page URL).
    
    $param_array = explode("&", $auctionally_parameters_code_val);

    foreach ($param_array as $value) {

        // These are only short strings so regex shoud be okay.
        $regex = '/prp=(.*?)$/';

        // If it has a value then pass it on, it could be empty too.
        if (preg_match_all($regex, $value, $matches)) { 

            $auctionally_passing_val = $matches[1][0];

            // Server does not need the prp parameter so remove it.
            $auctionally_parameters_code_val = str_replace("&" . $matches[0][0], "", $auctionally_parameters_code_val);

        }
        else
            $auctionally_passing_val = "";
    }

    // 2. Insert the script needed for the embedded shop.
	
    return '<script id="aa-shop-script" type="text/javascript" src="https://auctionally.co.uk/builder/auctionallyget.php" data-look="' . $auctionally_parameters_code_val . '" data-pass="' . $auctionally_passing_val . '"></script><div id="aa-shop"></div>';    
}

add_shortcode( 'auctionally', 'auctionally_shortcode_func' );
add_action( 'admin_menu', 'auctionally_plugin_menu' );


// Menu.

function auctionally_plugin_menu() {
	add_options_page( 'AuctionAlly Options', 'AuctionAlly', 'manage_options', 'auctionally-unique-identifier', 'auctionally_plugin_options' );
}


// Checks the parameters code input from the admin settings page.

function auctionally_admin_san_val($input_string_params) {

    // For error handling.
    $validity_errors = array();
    $flag_error = false;

    // For storing only the parameters that have been sanitised and validated. Aything else will just be ignored.
    $validated_parameters = array();

    // A list to check at the end to make sure all required parameters are present.
    $validated_parameters_checklist = array();

    // Accepted paramater names arrays.
    $accepted_string_er = array('er');
    $accepted_string_eu = array('eu');
    $accepted_string_hex = array('sb', 'bc', 'ib', 'tb', 'pc', 'fc');
    $accepted_int = array('iw', 'ih', 'im', 'b', 'br', 'bdt', 'bdr', 'bdb', 'bdl', 'iah', 'iwm', 'ihm', 'ip', 'th', 'fs', 'pp', 'sa');
    $accepted_string_align = array('ia', 'ta', 'pa');
    $accepted_string_passing = array('prp');


    // 1. Test if the parameter names are recognised.

    // Get all the recognised parameter names.
    $accepted_params_names = array_merge($accepted_string_er, $accepted_string_eu, $accepted_string_hex, $accepted_int, $accepted_string_align, $accepted_string_passing);
   
    // Split the 'Parameters Code' that the user has submited into name/value pairs.
    $input_string_params_arr = explode("&", $input_string_params);

    // Loop through each parameter submitted.
    foreach ($input_string_params_arr as $input_param) {

        // Separate the names and values.
        $name_to_test_split = explode("=", $input_param);
        $this_name = $name_to_test_split[0];
        $this_value = $name_to_test_split[1];

        // Reject unrecognised parameter names.
        if (!in_array($this_name, $accepted_params_names)) {
            $validity_errors[] = $this_name . ' is unrecognised.';
            $flag_error = true;
        }
        else {

            // 2. Test if the parameter names have the right characters in their values.

            switch ($this_name) {

                // The region value.
                case in_array($this_name, $accepted_string_er):

                    $test_value = preg_replace('/[^A-Z\-]/', '', $this_value);

                    if ($test_value !== $this_value) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;

                // The username value.
                case in_array($this_name, $accepted_string_eu):

                    $test_value = preg_replace('/[^0-9A-Za-z\-_\.]/', '', $this_value);

                    if ($test_value !== $this_value) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;

                // Hex values.
                case in_array($this_name, $accepted_string_hex):

                    $test_value = preg_replace('/[^0-9a-zA-Z]/', '', $this_value);

                    if ($test_value !== $this_value) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;

                // Int values.
                case in_array($this_name, $accepted_int):

                    $test_value = preg_replace('/[^0-9]/', '', $this_value);

                    if ($test_value !== $this_value) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;

                // Alignent strings.
                case in_array($this_name, $accepted_string_align):

                    if (!in_array($this_value, array('left', 'center', 'right'))) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;

                // Passed URL parameters string.
                case in_array($this_name, $accepted_string_passing):

                    $test_value = preg_replace('/[^0-9a-zA-Z,\_\- ]/', '', $this_value);

                    if ($test_value !== $this_value) {
                        $validity_errors[] = $this_name . ': ' . $this_value . ' contains invalid character(s).';
                        $flag_error = true;
                    }
                    else  {
                        $validated_parameters_checklist[] = $this_name;
                        $validated_parameters[] = $this_name . "=" . $this_value;
                    }

                    break;
            }
        }
    }


    // 3. Check that the total number of parameters is correct.

    if (count($validated_parameters) != count($accepted_params_names)) {

        $validity_errors[] = 'There are not the correct number of parameters.';
        $flag_error = true;
    };

    // 4. Check that the user has all the required parameters.

    foreach ($accepted_params_names as $value) {
        if (!in_array($value, $validated_parameters_checklist)) {
            $validity_errors[] = $value . ' is missing.';
            $flag_error = true;
        }
    }

    // 5. Was it a pass or fail? Return 1 for pass and 0 for fail, along with error messages.

    if ($flag_error === false)
        return array('validated' => 1, 'validated_string' => implode("&", $validated_parameters));
    else
        return array('validated' => 0, 'error_messages' => $validity_errors);
}

function auctionally_plugin_options() {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    // variables for the field and option names 
    $opt_name_params = 'auctionally_parameters_code';    
    $data_field_name_params = 'auctionally_parameters_code';  
    $hidden_field_name = 'auctionally_submit_hidden';

    // Read in existing option value from database
    $opt_val_params = get_option( $opt_name_params );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y' ) {

        // Nonce check.
        check_admin_referer('auctionally-name-action-settings-action', 'auctionally-name-action-settings-nonce');

        $opt_val_params = trim($_POST[$data_field_name_params]);

        // Sanitation and validating.
        $checked_input = auctionally_admin_san_val($opt_val_params);

        if ($checked_input['validated'] === 1) {

            // Save the posted value in the database
            update_option($opt_name_params, $checked_input['validated_string']);
            
            // Put a "settings saved" message on the screen
            echo '
            <div class="updated"><p><strong>' , _e('Settings saved.', 'menu-auctionally') , '</strong></p></div>';
        }
        else {

            foreach ($checked_input['error_messages'] as $value) {
                $alert .= " " . $value;
            }

            // Send the error messages.
            echo '
            <div class="error"><p><strong>', _e($alert, 'menu-auctionally') , '</strong></p></div>';
        }
    }

    // Now display the settings editing screen
    echo '<div class="wrap">';

    // header
    echo "<h2>" . __( 'Auctionally Settings', 'menu-auctionally' ) . "</h2>";

    // settings form
    
    ?>

<form name="form1" method="post" action="">

<?php
// Set up nonce.
if ( function_exists('wp_nonce_field') ) 
    wp_nonce_field('auctionally-name-action-settings-action', 'auctionally-name-action-settings-nonce'); 
?>

<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<table id="auctionally-settings-table">

    <tr>
        <td>
            <?php _e("Parameters Code:", 'menu-auctionally' ); ?> 
        </td>
        <td>
            <textarea name="<?php echo $data_field_name_params; ?>" rows="6" cols="80"><?php echo stripslashes($opt_val_params); ?></textarea>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <a href="https://auctionally.co.uk/designer/designer.php?<?php echo stripslashes($opt_val_params); ?>" target= "_blank">Edit this Design</a>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <p class="submit">
                <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
            </p>
        </td>
    </tr>

</table>
</form>
</div>

<hr />

<div class="wrap" style="background: #eee; padding: 20px; border: solid 1px #ccc">

        <h2>Instructions</h2>
        
        <p>The shop designer can be found on our site here: <a href="https://auctionally.co.uk">AuctionAlly</a></p>

        <p>Copy the *Wordpress* code from the AuctionAlly Designer ("Get Embed Code") button into the <em>Parameters Code</em> field above and click "Save Changes".</p>

        <p>On the page or post you want the shop to appear just enter the shortcode <strong>[auctionally]</strong></p>

        <p>The software has been designed to inherit styling from your theme as much as possible.</p>

        <p>It is impossible to know how every theme will react, so if it looks a mess come over to the support forum at 
            the <a href="https://auctionally.co.uk">AuctionAlly Website</a>, and we'll take a look :)</p>

        <p>At the time of writing this (June 2019) this plugin and service is in an open Beta stage.</p>
    </div>

<?php

}
