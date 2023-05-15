    <?php
    class BasicCredWidget extends WP_Widget
    {
        // ...
        public function __construct()
        {
            parent::__construct(
                'BasicLogin_Register', // Widget ID
                'BasicCredentials', // Widget name
                array('description' => 'Description of my widget') // Widget description
            );
        }
        // Widget front-end display
        public function widget($args, $instance)
        {
            // ccs Load
            wp_enqueue_style('widget-styling', plugin_dir_url(__FILE__) . 'includes/css/styling.css');

            $display_option = isset($instance['display_option']) ? $instance['display_option'] : '';

            // Check if the user is logged in
            $is_logged_in = is_user_logged_in();                    //Logged In
            $is_admin_in = current_user_can('administrator');       //Admin
            $image_url = plugin_dir_url(__FILE__) . 'images/checkmark.png';
            // Display different content based on the selected option and user login status
            if ($display_option === 'A') {
                if (
                    $is_logged_in && !$is_admin_in
                ) {
                    echo '<img src="' . esc_url($image_url) . '" alt="Checkmark">';
                } else {
                    $login_form = $this->generate_login_form();
                    echo $login_form;
                }
            } elseif ($display_option === 'B') {
                if ($is_logged_in && !$is_admin_in) {
                    $image_url = plugin_dir_url(__FILE__) . 'images/checkmark.png';
                } else {
                    $registration_form = $this->generate_registration_form();
                    echo $registration_form;
                }
            }
        }

        // Widget back-end configuration form
        public function form($instance)
        {

            $display_option = isset($instance['display_option']) ? $instance['display_option'] : '';
            $widget_id      = $this->id;

            // Display the form field to select the option
    ?>
            <p>
                <label for="<?php echo $this->get_field_id('display_option'); ?>">Display Option:</label>
                <select id="<?php echo $this->get_field_id('display_option'); ?>" name="<?php echo $this->get_field_name('display_option'); ?>">
                    <option value="A" <?php selected($display_option, 'A'); ?>>Login form</option>
                    <option value="B" <?php selected($display_option, 'B'); ?>>Register form</option>
                </select>
            </p>
            <input type="hidden" name="<?php echo $this->get_field_name('widget_id'); ?>" value="<?php echo $widget_id; ?>">
    <?php
        }
        // Generate the login form HTML
        private function generate_login_form()
        {
            $login_form = '<div class="form">';
            $login_form .= '<h1>Login</h1>';
            $login_form .= '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
            $login_form .= '<input type="hidden" name="action" value="my_widget_login">';
            $login_form .= wp_nonce_field('my_widget_login_nonce', 'my_widget_login_nonce', true, false);
            $login_form .= '<input type="text" name="username" placeholder="Username"><br>';
            $login_form .= '<input type="password" name="password" placeholder="Password"><br>';
            $login_form .= '<input type="submit" value="Log In">';
            $login_form .= '</form>';
            $login_form .= '</div>';

            return $login_form;
        }
        private function generate_registration_form()
        {
            $registration_form = '<div class="form">';
            $registration_form .= '<h1>Register</h1>';
            $registration_form .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
            $registration_form .= '<input type="hidden" name="action" value="my_widget_register">';
            $registration_form .= wp_nonce_field('my_widget_register_nonce', 'my_widget_register_nonce', true, false);
            $registration_form .= '<input type="text" name="username" placeholder="Username"><br>';
            $registration_form .= '<input type="password" name="password" placeholder="Password"><br>';
            $registration_form .= '<input type="email" name="email" placeholder="Email"><br>';
            $registration_form .= '<input type="submit" value="Register">';
            $registration_form .= '</form>';
            $registration_form .= '</div>';

            // Check if there is a registration error message
            if (isset($_GET['registration_error'])) {
                $error_message = sanitize_text_field($_GET['registration_error']);
                $registration_form .= '<p class="registration-error">' . esc_html($error_message) . '</p>';
            }

            return $registration_form;
        }

        // Process form submission
        public function process_login_form()
        {
            // Check if the form submission is valid
            if (isset($_POST['action']) && $_POST['action'] === 'my_widget_login' && wp_verify_nonce($_POST['my_widget_login_nonce'], 'my_widget_login_nonce')) {
                $username = sanitize_user($_POST['username']);
                $password = $_POST['password'];

                $credentials = array(
                    'user_login'    => $username,
                    'user_password' => $password,
                );

                // Attempt to log the user in
                $user = wp_signon($credentials, false);

                // Check if login was successful
                if (!is_wp_error($user)) {
                    // User logged in successfully
                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID);
                    do_action('wp_login', $user->user_login);

                    // Redirect the user to the homepage or a specific page
                    wp_redirect(home_url());
                    exit;
                } else {
                    // Login failed, display an error message
                    echo 'Invalid username or password. Please try again.';
                }
            }
        }
        public function process_registration_form()
        {
            if (isset($_POST['action']) && $_POST['action'] === 'my_widget_register' && wp_verify_nonce($_POST['my_widget_register_nonce'], 'my_widget_register_nonce')) {
                $username = sanitize_user($_POST['username']);
                $password = $_POST['password'];
                $email = sanitize_email($_POST['email']);

                // Create the new user
                $user_id = wp_create_user($username, $password, $email);

                if (!is_wp_error($user_id)) {
                    echo '<p>Registration successful. You can now log in.</p>';

                    // Get the current page URL
                    $redirect_url = $_SERVER['REQUEST_URI'];

                    // Redirect the user to the current page
                    wp_safe_redirect($redirect_url);
                    exit;
                } else {
                    $registration_error = $user_id->get_error_message(); // Get the registration error message
                    $registration_form = $this->generate_registration_form(); // Retrieve the registration form HTML

                    // Append the error message to the registration form
                    $registration_form .= '<p class="registration-error">' . esc_html($registration_error) . '</p>';

                    // Output the registration form with the error message
                    echo $registration_form;
                }
            }
        }


        // Save widget options
        public function update($new_instance, $old_instance)
        {
            $instance                     = $old_instance;
            $instance['display_option']   = sanitize_text_field($new_instance['display_option']);
            $instance['widget_id']        = $new_instance['widget_id'];

            return $instance;
        }

        // ...
    }
