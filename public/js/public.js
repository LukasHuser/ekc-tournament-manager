(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

  $( function() {
	 
	 /* validates if an entered result does not exceed the maximum number of points. */
	 var validateResult = function() {      
		var is_valid = true;
		var max_value;
		$("#ekc-result-form").each(function() {
		  var result_id = $(this).data("resultid");
          if ( result_id ) {
            var team1_score = parseInt( $("#team1-score-" + result_id).val(), 10);
            var team2_score = parseInt( $("#team2-score-" + result_id).val(), 10);
			max_value = parseInt( $("#team1-score-" + result_id).attr("max"), 10);
			if ( team1_score + team2_score > max_value ) {
			  is_valid = false;
			}
          }
        });

		if (!is_valid) {
		  $("#ekc-result-validation").html("<br>The sum of both scores must not be greater than " + max_value);
		}

        return is_valid;
      };

      $( "#ekc-result-form" ).submit(function(event){
        return validateResult();
      }); 

  });

})( jQuery );
