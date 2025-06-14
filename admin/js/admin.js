(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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
    /* use jquery css style for all form inputs in admin view */
    $('input').addClass("ui-widget ui-widget-content ui-corner-all");

    /* Confirmation popup for deletions */
    /* matches all links of the following form: <span class="delete"><a><a/></span> */
    $('span.delete a').confirm({
      title: "Confirm deletion",
      content: "Deletions cannot be reverted. Do you want to continue?",
      useBootstrap: false,
      buttons: {
        delete: function(){
            location.href = this.$target.attr('href');
        },
        cancel: function(){}
    }
    });

    /* add datepicker  */
    $( ".ekc-datepicker" ).datepicker({
      dateFormat: "yy-mm-dd"
    });

    /* add button */
    $( ".ekc-button" ).button();

    /* define selectmenu */
    $( ".ekc-selectmenu" ).selectmenu();

    /************************************************************************/

    /* country-selectmenu: extend selectmenu */
    $.widget( "ekc.country_selectmenu", $.ui.selectmenu, {

      _create: function() {
        var selectedValue = this.element.find( "option:selected" ).attr("value");
        this._super();
        var buttonId = "#" + this.ids.button;
        $(buttonId).addClass( $("#" + this.ids.element).attr("class") );
        $(".ui-selectmenu-text", buttonId).prepend( $('<span>&nbsp;&nbsp;</span>') );
        $(".ui-selectmenu-text", buttonId).prepend( $('<span></span>').addClass( "flag-icon flag-icon-" + selectedValue ) );
      }
    });

    /* define country-selectmenu */
    $( ".ekc-country-selectmenu" ).country_selectmenu({
      open: function() {
        $('div.ui-selectmenu-menu li.ui-menu-item div.ui-menu-item-wrapper').each(function(idx){
          if ($('span', this).length == 0) {
            $(this).prepend( $('<span>&nbsp;&nbsp;</span>') );
            $(this).prepend( $('<span></span>').addClass( $('select option').eq(idx).attr('class') ) );
          }
        })
        $('div.ui-selectmenu-menu').each(function(idx){
          $(this).addClass( $('select').eq(idx).attr('class') )
        })
      },

      select: function( event, ui ) {
        var buttonId = "#" + this.id + "-button";
        if ($('.flag-icon', buttonId).length == 0) {
          $(".ui-selectmenu-text", buttonId).prepend( $('<span>&nbsp;&nbsp;</span>') );
          $(".ui-selectmenu-text", buttonId).prepend( $('<span></span>').addClass( "flag-icon flag-icon-" + ui.item.value ) );
        }
        else {
          // replace existing classes with new value
          $('.flag-icon', buttonId).attr( "class", "flag-icon flag-icon-" + ui.item.value );
        }
      }
    });

    /***************************************************************************/

    /* combobox: extend autocomplete */
    /* based on demo from http://jqueryui.com/autocomplete/#combobox */
      $.widget( "ekc.teams_combobox", {
        _create: function() {
          this.dataLoaded = false;

          this.wrapper = $( "<span>" )
            .addClass( "ekc-combobox-wrapper" )
            .insertAfter( this.element );
   
          this.element.hide();
          this._createAutocomplete();
          this._createShowAllButton();
        },

        loadData: function() {
          if ( ekc.teamsDropDownData ) {
            var selected = this.element.children( ":selected" ).val();
            $("option", this.element ).remove();
            $("<option>")
              .appendTo( this.element )
              .attr( "value", "selection_none" );

            for (var teamId in ekc.teamsDropDownData) {
              if ( selected === teamId ) {
                $("<option>")
                  .appendTo( this.element )
                  .attr( "value", teamId )
                  .attr( "selected", "")
                  .text( ekc.teamsDropDownData[teamId] );
              }
              else {
                $("<option>")
                .appendTo( this.element )
                .attr( "value", teamId )
                .text( ekc.teamsDropDownData[teamId] );
              }
            }
            this.dataLoaded = true;
          }
        },
   
        _createAutocomplete: function() {
          var selected = this.element.children( ":selected" ),
            value = selected.val() ? selected.text() : "";
   
          this.input = $( "<input>" )
            .appendTo( this.wrapper )
            .val( value )
            .attr( "title", "" )
            .addClass( "ekc-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
            .autocomplete({
              delay: 0,
              minLength: 0,
              source: $.proxy( this, "_source" )
            })
            .tooltip({
              classes: {
                "ui-tooltip": "ui-state-highlight"
              }
            });
   
          this._on( this.input, {
            autocompleteselect: function( event, ui ) {
              ui.item.option.selected = true;
              this._trigger( "select", event, {
                item: ui.item.option
              });
            },
   
            autocompletechange: "_removeIfInvalid"
          });
        },
   
        _createShowAllButton: function() {
          var input = this.input,
            wasOpen = false;
   
          $( "<a>" )
            .attr( "tabIndex", -1 )
            .attr( "title", "Show All Items" )
            .tooltip()
            .appendTo( this.wrapper )
            .button({
              icons: {
                primary: "ui-icon-triangle-1-s"
              },
              text: false
            })
            .removeClass( "ui-corner-all" )
            .addClass( "ekc-combobox-button ui-corner-right" )
            .on( "mousedown", function() {
              wasOpen = input.autocomplete( "widget" ).is( ":visible" );
            })
            .on( "click", function() {
              input.trigger( "focus" );
   
              // Close if already visible
              if ( wasOpen ) {
                return;
              }
   
              // Pass empty string as value to search, for displaying all results
              input.autocomplete( "search", "" );
            });
        },
   
        _source: function( request, response ) {
          if ( !this.dataLoaded ) {
            this.loadData();
          }

          var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
          response( this.element.children( "option" ).map(function() {
            var text = $( this ).text();
            if ( this.value && ( !request.term || matcher.test(text) ) )
              return {
                label: text,
                value: text,
                option: this
              };
          }) );
        },
   
        _removeIfInvalid: function( event, ui ) {
   
          // Selected an item, nothing to do
          if ( ui.item ) {
            return;
          }
   
          // Search for a match (case-insensitive)
          var value = this.input.val(),
            valueLowerCase = value.toLowerCase(),
            valid = false;
          this.element.children( "option" ).each(function() {
            if ( $( this ).text().toLowerCase() === valueLowerCase ) {
              this.selected = valid = true;
              return false;
            }
          });
   
          // Found a match, nothing to do
          if ( valid ) {
            return;
          }
   
          // Remove invalid value
          this.input
            .val( "" )
            .attr( "title", value + " didn't match any item" )
            .tooltip( "open" );
          this.element.val( "" );
          this._delay(function() {
            this.input.tooltip( "close" ).attr( "title", "" );
          }, 2500 );
          this.input.autocomplete( "instance" ).term = "";
        },
   
        _destroy: function() {
          this.wrapper.remove();
          this.element.show();
        }
      });
   
      $( ".ekc-teams-combobox" ).teams_combobox();

      /***************************************************************************/

      /* save a single tournament result (instead of submitting the whole form) */
      var postTournamentResult = function( a ) {
        var result_id = $(a).data("resultid");
        var nonce = $(a).data("nonce");

        var team1_score = $("#team1-score-" + result_id);
        var team2_score = $("#team2-score-" + result_id);
        var is_result_missing = (team1_score.val() === "") || (team2_score.val() === "");
        if (is_result_missing) {
          team1_score.parent().addClass("ekc-result-missing");
          team2_score.parent().addClass("ekc-result-missing");
        }
        else {
          team1_score.parent().removeClass("ekc-result-missing");
          team2_score.parent().removeClass("ekc-result-missing");
        }

        var post_data = {
          "action": "ekc_admin_swiss_system_store_result",
          "resultid": result_id,
          "ekc-nonce": nonce
        };
        post_data["pitch-" + result_id] = $("#pitch-" + result_id).val();
        post_data["team1-" + result_id] = $("#team1-" + result_id).val();
        post_data["team2-" + result_id] = $("#team2-" + result_id).val();
        post_data["team1-score-" + result_id] = team1_score.val();
        post_data["team2-score-" + result_id] = team2_score.val();
        post_data["team1-placeholder-" + result_id] = $("#team1-placeholder-" + result_id).val();
        post_data["team2-placeholder-" + result_id] = $("#team2-placeholder-" + result_id).val();

        $.post(ekc_ajax.ajax_url, post_data, function( result ) {
          $( "#post-result-" + result_id ).html( result );
        });
      };

      $( ".ekc-post-result" ).click(function(){
        postTournamentResult( $(this) );
        return false;
      }); 

      /* validate if all results have been entered. */
      var validateResults = function() {      
        var has_missing_results = false;
        var missing_results_text = "The following results are missing: ";
        $(".ekc-result-missing").each(function() {
          var result_id = $(this).data("resultid");
          if ( result_id ) {
            var team1_id = $("#team1-" + result_id).val();
            var team2_id = $("#team2-" + result_id).val();
            var pitch = $("#pitch-" + result_id).val();
            var team1_name = team1_id;
            var team2_name = team2_id; 
            if ( ekc.teamsDropDownData ) {
              team1_name = ekc.teamsDropDownData[team1_id];
              team2_name = ekc.teamsDropDownData[team2_id];
            }
            if ( has_missing_results ) {
              // ignore on first result
              missing_results_text += ", ";
            }
            missing_results_text += team1_name + " vs " + team2_name; 
            if ( pitch ) {
              missing_results_text += " (pitch " + pitch + ")"; 
            }
            has_missing_results = true;
          }
        });

        if ( has_missing_results ) {
          $( "#swiss-system-new-round-form-validation-text" ).text(missing_results_text);
        }
        else {
          $( "#swiss-system-new-round-form-validation-text" ).text("");
        }
        
        return !has_missing_results;
      };

      $( "#swiss-system-new-round-form" ).submit(function(event){
        return validateResults();
      });

      /***************************************************************************/

      /* store a single result and advance a team to the next elimination round (do not submit the whole form) */
      var eliminationBracketAdvance = function( a ) {
        var result_type = $(a).data("result-type");
        var nonce = $(a).data("nonce");

        var team_index_advance = $(a).data("team-index-advance"); // returns an integer (1 or 2), not a string
        var team1_id = $("#team1-" + result_type).val();
        var team2_id = $("#team2-" + result_type).val();
        var team_id_advance = null;
        if (team_index_advance === 1) {
          team_id_advance = team1_id;
        }
        else if (team_index_advance === 2) {
          team_id_advance = team2_id;
        }

        var elimination_bracket_form = $("#elimination-bracket-form");
        var tournament_id = elimination_bracket_form.find("input#tournamentid").val();
        var bracket_type = elimination_bracket_form.find("input#bracket").val();

        var post_data = {
          "action": "ekc_admin_bracket_advance",
          "tournamentid": tournament_id,
          "bracket": bracket_type,
          "team-id-advance": team_id_advance,
          "result-type": result_type,
          "ekc-nonce": nonce
        };
        post_data["pitch-" + result_type] = $("#pitch-" + result_type).val();
        post_data["team1-" + result_type] = team1_id;
        post_data["team2-" + result_type] = team2_id;
        post_data["team1-score-" + result_type] = $("#team1-score-" + result_type).val();
        post_data["team2-score-" + result_type] = $("#team2-score-" + result_type).val();
        post_data["team1-placeholder-" + result_type] = $("#team1-placeholder-" + result_type).val();
        post_data["team2-placeholder-" + result_type] = $("#team2-placeholder-" + result_type).val();

        $.post(ekc_ajax.ajax_url, post_data, function( json_result ) {
          var result = $.parseJSON(json_result);
          $( "#bracket-advance-" + result_type ).html( result.html );

          // advance to next round
          if (result.hasOwnProperty("team_index") && result.hasOwnProperty("result_type") && ekc.teamsDropDownData) {
            var selectNode = $( "#team" + result.team_index + "-" + result.result_type );
            var combobox_widget = selectNode.teams_combobox("instance");
            if (!combobox_widget.dataLoaded) {
              $("<option>")
                .appendTo( selectNode )
                .attr( "value", team_id_advance )
                .attr( "selected", "")
                .text( ekc.teamsDropDownData[team_id_advance] );
            }
            selectNode.val(team_id_advance);
            combobox_widget.input.val(ekc.teamsDropDownData[team_id_advance]);
          }
        });
      };

      $( ".ekc-bracket-advance" ).click(function(){
        eliminationBracketAdvance( $(this) );
        return false;
      }); 
      
  });

})( jQuery );

