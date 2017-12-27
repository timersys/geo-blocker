<?php
/**
* Grab geobl settings
* @return mixed|void
*/
function geobl_settings(){
	return apply_filters('geobl/opts', get_option( 'geobl_settings' ) );
}