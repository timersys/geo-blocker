(function( $ ) {
	'use strict';

	if( $('.geobl-ajax').length ) {
        var data = {
                action : 'geo_redirects',
                pid : geobl.pid,
                referrer : document.referrer,
                query_string : document.location.search,
                is_category : geobl.is_category,
                is_archive : geobl.is_archive,
                is_front_page : geobl.is_front_page,
                is_search : geobl.is_search
            }
            ,success_cb = function(response) {
                if( response && response.url ){
                    $('.geobl-ajax').show();
                    setTimeout(function(){
                        location.replace(response.url)
                    },2000);
                }
            },
            error_cb 	= function (data, error, errorThrown){
                console.log('Geo Redirects error: ' + error + ' - ' + errorThrown);
            }
        request(data, success_cb, error_cb);
	}
    /**
     * Ajax requests
     * @param data
     * @param url
     * @param success_cb
     * @param error_cb
     * @param dataType
     */
    function request(data, success_cb, error_cb ){
        // Prepare variables.
        var ajax       = {
                url:      geobl.ajax_url,
                data:     data,
                cache:    false,
                type:     'POST',
                dataType: 'json',
                timeout:  30000
            },
            dataType   = dataType || false,
            success_cb = success_cb || false,
            error_cb   = error_cb   || false;


        // Set success callback if supplied.
        if ( success_cb ) {
            ajax.success = success_cb;
        }

        // Set error callback if supplied.
        if ( error_cb ) {
            ajax.error = error_cb;
        }

        // Make the ajax request.
        $.ajax(ajax);

    }
})( jQuery );
