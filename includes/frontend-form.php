<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since Twenty Nineteen 1.0
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php
			global $reg_errors;
			$reg_errors = new WP_Error;

			if( $_POST ) {
				if ( empty( $_POST['username'] ) ) {
				    $reg_errors->add('field', 'Required form field is missing');
				}
				
				if ( is_wp_error( $reg_errors ) ) {
				    foreach ( $reg_errors->get_error_messages() as $error ) {
				        echo '<div>';
				        echo '<strong>ERROR</strong>:';
				        echo $error . '<br/>';
				        echo '</div>';
				    }	 
				}
			}
			// Start the Loop.
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content/content', 'page' );

				?>
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
				    <div>
				    <label for="username">Username <strong>*</strong></label>
				    <input type="text" name="username" value="<?php echo isset( $_POST['username'] ) ? $username : null; ?>">
				    </div>
				     
				    <div>
				    <label for="password">Password <strong>*</strong></label>
				    <input type="password" name="password" value="<?php echo isset( $_POST['password'] ) ? $password : null; ?>">
				    </div>
				     
				    <div>
				    <label for="email">Email <strong>*</strong></label>
				    <input type="text" name="email" value="<?php echo isset( $_POST['email']) ? $email : null; ?>">
				    </div>
				     
				    <div>
				    <label for="website">Website</label>
				    <input type="text" name="website" value="<?php echo isset( $_POST['website']) ? $website : null; ?>">
				    </div>
				     
				    <div>
				    <label for="firstname">First Name</label>
				    <input type="text" name="fname" value="<?php echo isset( $_POST['fname']) ? $first_name : null; ?>">
				    </div>
				     
				    <div>
				    <label for="website">Last Name</label>
				    <input type="text" name="lname" value="<?php echo isset( $_POST['lname']) ? $last_name : null; ?>">
				    </div>
				     
				    <div>
				    <label for="nickname">Nickname</label>
				    <input type="text" name="nickname" value="<?php echo isset( $_POST['nickname']) ? $nickname : null; ?>">
				    </div>
				     
				    <div>
				    <label for="bio">About / Bio</label>
				    <textarea name="bio"><?php echo isset( $_POST['bio']) ? $bio : null; ?></textarea>
				    </div>
				    <input type="submit" name="submit" value="Register"/>
				</form>
				<?php
			endwhile; // End the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
