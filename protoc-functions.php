<?php
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
            $table_of_contents .= '<style>#table-of-contents {border-radius: 5px;box-shadow: 1px 1px 14px 1px rgb(0 0 0 / 20%);background-color: white;padding: 15px;}#table-of-contents a {text-decoration: none;}#table-of-contents a:hover {text-decoration: underline;}</style><div id="table-of-contents"><p><strong>Table of Contents</strong></p><ul>';
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
        // Add table of contents before first heading
    $content = preg_replace('/(<h[2-6].*?>)/', $table_of_contents.'$1', $content, 1);
    }
  }
    return $content;
}
function saur8_toc_shortcode() {
    return saur8_display_toc( get_the_content() );
}
?>
