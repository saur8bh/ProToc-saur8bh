<?php
// Add settings page
function saur8bh_add_settings_page() {
	add_options_page( 'ProToc Settings', 'ProToc Settings', 'manage_options', 'protoc-settings', 'saur8bh_render_settings_page' );
}
add_action( 'admin_menu', 'saur8bh_add_settings_page' );

// Render settings page
function saur8bh_render_settings_page() {
	?>
	<div class="wrap">
		<h1>ProToc Settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'protoc-settings-group' ); ?>
			<?php do_settings_sections( 'protoc-settings-group' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Table of Contents Method</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span>Table of Contents Method</span></legend>
							<label for="protoc_automatic">
								<input type="radio" name="protoc_method" id="protoc_automatic" value="automatic" <?php checked( get_option( 'protoc_method' ), 'automatic' ); ?>>
								Automatic (insert table of contents before first H2 heading)
							</label><br>
							<label for="protoc_shortcode">
								<input type="radio" name="protoc_method" id="protoc_shortcode" value="shortcode" <?php checked( get_option( 'protoc_method' ), 'shortcode' ); ?>>
								Shortcode (insert table of contents with [saur8_toc] shortcode)
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// Register settings
function saur8bh_register_settings() {
	register_setting( 'protoc-settings-group', 'protoc_method' );
}
add_action( 'admin_init', 'saur8bh_register_settings' );
?>
