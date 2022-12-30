CHANGELOG ekc-tournament
========================

## TODO
[3] team / tournament: Re-design form input/label alignment  
[3] performance: drop down lists as key / value (integer keys instead of string keys)
[2] drop down widget too small for long team names

# Version 1.2.19
[x] Compatibilty for Elementor 3.9.2

# Version 1.2.18
[x] Swiss System: Pitch limit mode, if number of teams exceeds number of available pitches.
[x] Swiss System: Avoid duplicate execution of non-idempotent actions: Confirmation box for forms (prevent double click). URL redirect after POST and/or GET requests (prevent page reload).

# Version 1.2.17
[x] Support for Contact Form 7 plugin

# Version 1.2.16
[x] Bugfix: missing flags on elimination bracket
[x] Elimination bracket: pre-defined positions in bracket for each rank
[x] Swiss System: Timer for tie break
[x] Registration fees: mark paid fees

# Version 1.2.15
[x] Support for field 'club' in Elementor forms

# Version 1.2.14
[x] Additional attribute on teams: sports club / city (optional column for registration table)
[x] Removed attributes 'camping count' and 'breakfast count' from teams overview table
[x] Swiss System: save single result: mark missing results
[x] Swiss System: validate missing results before starting next round
[x] Swiss System: validate result on shareable link page

# Version 1.2.13
[x] Fix timer: off by one minute

# Version 1.2.12
[x] Updated css for jQuery UI Version 1.12.1
[x] Confirmation popups for delete operations (using jQuery Confirm)
[x] Swiss System: Possibility to delete the current round (i.e. when a round has been started too early, with missing results etc.)

# Version 1.2.11
[x] Swiss System: define a starting pitch number for a tournament (needed when two tournaments take place in parallel, e.g. pro and amateur)

# Version 1.2.10
[x] Validation: maximum number of points per round (needed for input from shareable links)

# Version 1.2.9
[x] shareable links page: show e-mail address for each team
[x] shortcodes (ranking, elimination bracket, registered teams etc.) allow to hide country columns and flags
[x] refactoring of elimination bracket html code (remove duplicated copy/paste code)
[x] shortcode ekc-link: show timer (without explicitly providing tournament as parameter)
[x] team registration: country not mandatory

# Version 1.2.8
[x] swiss system: support to generate random seeding scores
[x] shareable links: sender e-mail address

## Version 1.2.7
[x] shareable links: timer for current round on personalized pages

## Version 1.2.6
[x] avoid duplicate matches. Use blossom algorithm for minimum weight matching in undirected graphs
[x] store each result individually. Needed to store results in parallel (two or more users, and for shareable links)
[x] 2+vs2+ support for virtual EKC
[x] shareable links: individual pages for each team via shareable links. All rounds and results per team. Allow to report result of current round.

## Version 1.2.5
[x] team registration: extension for elementor forms to directly insert form content do database
[x] team registration: new attribute 'registration order' to manually control order in registration list and waiting list
[x] team registration: new menu on teams table to directly put a team on the waiting list (or remove it)
[x] admin: sortable and filterable tables in admin pages (tournaments, teams etc.)

## Version 1.2.4
[x] swiss system: ranking page in admin view, allows updating initial score, seeding score and virtual rank
[x] swiss system: allow initial score (for an accelerated swiss system)
[x] swiss system: additional ranking rounds after start of elimination bracket, aka virtual results (Part II)

## Version 1.2.3
[x] swiss system: additional ranking rounds after start of elimination bracket, aka virtual results (Part I)

## Version 1.2.2
[x] backup concept: Export / import of tournaments in JSON format. Auto backup option for tournaments

## Version 1.2.1
[x] Mark a team for waiting list (to allow a temporary limit for e.g. the hosting country)  

## Version 1.2.0
[x] performance: calculating swiss ranking table for > 100 teams is slow (mySql). Fixed: 200 teams load in less than 1 second (instead of over a minute)
[x] backend: ui cleanup and bugfix for dropdown boxes  
[x] performance: fixed issue with teams dropdown menu for > 100 teams  
[x] Add USA to available countries  
[x] swiss system: auto scroll to matches in table & auto refresh of page  
[x] tournament registration: camping & breakfast per team  
[x] swiss system: number of slide match rounds as parameter  
[x] swiss system: result 0:0 -> mark yellow