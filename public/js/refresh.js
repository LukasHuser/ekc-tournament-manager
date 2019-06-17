(function( $ ) {
	'use strict';

    $( window ).load(function() {
        schedule_refresh();
    });

    function schedule_refresh() {
        /* get refreshInterval in seconds from URL paramter 'refresh' */
        const refreshInterval = parseInt( new URLSearchParams( window.location.search ).get( 'refresh' ) );
        if (refreshInterval > 0) {
            window.setTimeout( function(){ window.location.reload(true); }, refreshInterval * 1000 );
        }
    }

})( jQuery );