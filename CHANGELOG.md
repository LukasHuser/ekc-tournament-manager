CHANGELOG EKC Tournament Manager
================================

## Version 2.1.2 / 2024-04-09
* Bugfix: Reporting results through shareable links does not work (except for logged-in users) 

## Version 2.1.1 / 2024-02-10
* Country drop down: support more countries

## Version 2.1.0 / 2024-02-04
* New shortcode for nation trophy
* New menu to create a new tournament as a copy of an existing tournament
* Elimination bracket: new menu to populate initial elimination bracket from swiss system ranking. New menu to delete all results.
* Swiss system: Calculate opponent score without considering direct matches
* Swiss system: new parameter for number of points for virtual results (when playing additional rounds)
* Swiss system: improve pairing in case of multiple maximum weight matchings. Use non-linear distance function for blossom algorithm.
* Result log: store team, date and time for results changed through shareable links
* Shareable links: success message when storing a result (use ajax call instead of post-redirect-get)
* Shareable links: allow modifications to the results through shareable links for up to 4 hours after the first result for a given round has been reported
* Shareable links e-mails: Increase upper limit to 100000 characters
* Performance: Added database index to shareable link ID
* Performance: Added database index to result table columns: stage, tournament_round, result_type
* Backup: Store shareable links when importing teams
* Post-redirect-get for elimination bracket form
* Admin: Added flag for Croatia
* Admin: Increased width of teams combobox
* Admin: Fix teams drop down: include all active teams (ignoring waiting list and maximum number of teams in tournament)
* Admin forms UI cleanup

## Version 2.0.5 / 2023-06-24
* Store backup data in wp-content/uploads instead of the plugin directory

## Version 2.0.4 / 2023-05-13
* Set WordPress stable tag to 2.0.4

## Version 2.0.3 / 2023-05-11
* Additional input validation
* Backups: Support for PHP 8

## Version 2.0.2 / 2023-04-23
* Update JsonMapper library (Support for PHP >= 7.4)

## Version 2.0.1 / 2023-04-20
* Use built-in tinymce editor for shareable link e-mails
* Custom tinymce plugin for emojis

## Version 2.0.0 / 2023-04-10
* Re-name plugin to "EKC Tournament Manager"
* Changed license to GPL v3
* Use EKA Logo in admin menu

## Version 1.2.19
* Compatibilty for Elementor 3.9.2

## Version 1.2.18
* Swiss System: Pitch limit mode, if number of teams exceeds number of available pitches.
* Swiss System: Avoid duplicate execution of non-idempotent actions: Confirmation box for forms (prevent double click). URL redirect after POST and/or GET requests (prevent page reload).

## Version 1.2.17
* Support for Contact Form 7 plugin

## Version 1.2.16
* Bugfix: missing flags on elimination bracket
* Elimination bracket: pre-defined positions in bracket for each rank
* Swiss System: Timer for tie break
* Registration fees: mark paid fees

## Version 1.2.15
* Support for field 'club' in Elementor forms

## Version 1.2.14
* Additional attribute on teams: sports club / city (optional column for registration table)
* Removed attributes 'camping count' and 'breakfast count' from teams overview table
* Swiss System: save single result: mark missing results
* Swiss System: validate missing results before starting next round
* Swiss System: validate result on shareable link page

## Version 1.2.13
* Fix timer: off by one minute

## Version 1.2.12
* Updated css for jQuery UI Version 1.12.1
* Confirmation popups for delete operations (using jQuery Confirm)
* Swiss System: Possibility to delete the current round (i.e. when a round has been started too early, with missing results etc.)

## Version 1.2.11
* Swiss System: define a starting pitch number for a tournament (needed when two tournaments take place in parallel, e.g. pro and amateur)

## Version 1.2.10
* Validation: maximum number of points per round (needed for input from shareable links)

## Version 1.2.9
* shareable links page: show e-mail address for each team
* shortcodes (ranking, elimination bracket, registered teams etc.) allow to hide country columns and flags
* refactoring of elimination bracket html code (remove duplicated copy/paste code)
* shortcode ekc-link: show timer (without explicitly providing tournament as parameter)
* team registration: country not mandatory

## Version 1.2.8
* swiss system: support to generate random seeding scores
* shareable links: sender e-mail address

## Version 1.2.7
* shareable links: timer for current round on personalized pages

## Version 1.2.6
* avoid duplicate matches. Use blossom algorithm for minimum weight matching in undirected graphs
* store each result individually. Needed to store results in parallel (two or more users, and for shareable links)
* 2+vs2+ support for virtual EKC
* shareable links: individual pages for each team via shareable links. All rounds and results per team. Allow to report result of current round.

## Version 1.2.5
* team registration: extension for elementor forms to directly insert form content do database
* team registration: new attribute 'registration order' to manually control order in registration list and waiting list
* team registration: new menu on teams table to directly put a team on the waiting list (or remove it)
* admin: sortable and filterable tables in admin pages (tournaments, teams etc.)

## Version 1.2.4
* swiss system: ranking page in admin view, allows updating initial score, seeding score and virtual rank
* swiss system: allow initial score (for an accelerated swiss system)
* swiss system: additional ranking rounds after start of elimination bracket, aka virtual results (Part II)

## Version 1.2.3
* swiss system: additional ranking rounds after start of elimination bracket, aka virtual results (Part I)

## Version 1.2.2
* backup concept: Export / import of tournaments in JSON format. Auto backup option for tournaments

## Version 1.2.1
* Mark a team for waiting list (to allow a temporary limit for e.g. the hosting country)  

## Version 1.2.0
* performance: calculating swiss ranking table for > 100 teams is slow (mySql). Fixed: 200 teams load in less than 1 second (instead of over a minute)
* backend: ui cleanup and bugfix for dropdown boxes  
* performance: fixed issue with teams dropdown menu for > 100 teams  
* Add USA to available countries  
* swiss system: auto scroll to matches in table & auto refresh of page  
* tournament registration: camping & breakfast per team  
* swiss system: number of slide match rounds as parameter  
* swiss system: result 0:0 -> mark yellow