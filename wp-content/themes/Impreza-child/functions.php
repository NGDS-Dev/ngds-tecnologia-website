<?php
/* Custom functions code goes here. */


function current_year_shortcode() {
    return date('Y');
}
add_shortcode('current_year', 'current_year_shortcode');