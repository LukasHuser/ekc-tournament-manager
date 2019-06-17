CHANGELOG ekc-tournament
========================

## TODO
[1] swiss system: teams already played -> mark red
[3] team / tournament: form input/label alignment  
[3] performance: drop down lists as key / value (integer keys instead of string keys)  

## Version 1.2.3
[1] swiss system: additional ranking rounds after start of elimination bracket

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