CHANGELOG ekc-tournament
========================

## TODO
[1] swiss system: teams already played -> mark red
[3] team / tournament: form input/label alignment  
[3] performance: drop down lists as key / value (integer keys instead of string keys)  
[1] 2+vs2+ support for virtual EKC

## Version 1.2.6
[1] store each result individually. Needed to store results in parallel (two or more users, and for shareable links)
[1] individual pages for each team via shareable links. All rounds and results per team. Allow to report result of current round.

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