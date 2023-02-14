<?php
/**
 * Plugin Name: ProToc By Saur8bh
 * Plugin URI: https://github.com/saur8bh/ProToc-saur8bh
 * Description: Generates a table of contents for your posts or pages based on headings.
 * Version: 1.0.0
 * Author: Saur8bh
 * Author URI: https://www.mrskt.com/
 */

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

function saur8_display_toc( $content ) {
  if ( is_singular( array( 'post', 'page' ) ) ) {
    $option = get_option( 'protoc_method' );
    $table_of_contents = '';

          if ( $option === 'automatic' || has_shortcode( $content, 'saur8_toc' ) ) {
        // Add ID's to the headings
        $count = 0;
        $content = preg_replace_callback( '/<h([2-6]).*>(.*)<\/h[2-6]>/i', function( $matches ) use ( &$count ) {
            $count++;
            return '<h' . $matches[1] . ' id="heading-' . $count . '">' . $matches[2] . '</h' . $matches[1] . '>';
        }, $content );

        // Generate table of contents
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
        $xpath = new DOMXPath( $dom );
        $headings = $xpath->query( '//h2|//h3|//h4|//h5|//h6' );

        if ( $headings->length > 0 ) {
            $table_of_contents .= '<div id="table-of-contents"><p><strong>Table of Contents</strong></p><ul>';
            $current_level = 2;
            $previous_level = 2;
            $toc_counter = 0;
            $stack = array();

            foreach ( $headings as $heading ) {
                $toc_counter++;

                // Determine heading level
                $current_level = (int) $heading->tagName[1];

                // Generate anchor
                $anchor = '<a href="#' . $heading->getAttribute( 'id' ) . '">' . $heading->nodeValue . '</a>';

                // Add to table of contents
                if ( $current_level > $previous_level ) {
                    $table_of_contents .= '<ul>';
                    array_push( $stack, '</ul>' );
                } elseif ( $current_level < $previous_level ) {
                    $table_of_contents .= str_repeat( array_pop( $stack ), $previous_level - $current_level ) . '</li>';
                } else {
                    $table_of_contents .= '</li>';
                }

                $table_of_contents .= '<li>' . $anchor;

                $previous_level = $current_level;
            }

            $table_of_contents .= str_repeat( array_pop( $stack ), $previous_level - 2 ) . '</li></ul></div>';
        }
    }

    if ( has_shortcode( $content, 'saur8_toc' ) ) {
        $content = str_replace( '[saur8_toc]', $table_of_contents, $content );
    } elseif ( $option === 'automatic' ) {
        // Add table of contents to content
        $content = $table_of_contents . $content;
    }
  }
    return $content;
}
add_filter( 'the_content', 'saur8_display_toc' );
	
function saur8_toc_shortcode() {
    return saur8_display_toc( get_the_content() );
}
add_shortcode( 'saur8_toc', 'saur8_toc_shortcode' );
