EKC Tournament Manager
======================

EKC Tournament Manager is a WordPress plugin that allows you to manage [Swiss system](https://en.wikipedia.org/wiki/Swiss-system_tournament) style tournaments, including registration of teams and players. 
It is developed for and used at the [EKC European Kubb Championships](https://kubbeurope.com). 

## Installation

1. Upload directory `ekc-tournament-manager` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage tournaments through the 'EKC Tournaments' menu in WordPress

or install the plugin 'EKC Tournament Manager' through the WordPress plugin store.

## Source Code

EKC Tournament Manager is free and open source software licensed under the [GPL v3 or later](http://www.gnu.org/licenses/gpl-3.0.html).

The source code is available on Github: https://github.com/LukasHuser/ekc-tournament-manager 

## WordPress Shortcode Reference

Most shortcodes take a `tournament` as a parameter.
This parameter corresponds to the *code name* field in the tournament form when creating a new tournament.
The *code name* of a tournament is a technical identifier and should be short and descriptive and contain no spaces.
Examples are `EKC18-1vs1` or `PKO-2020` or similar.

### ekc-teams

Allows to list registered teams and/or players for a given tournament displayed as a table.
The output will only include active teams.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`tournament`** | Tournament code name (as defined in tournament registration). Mandatory. | none |
| **`limit`** | The maximum number of teams to be shown in the table. If the tournament has a waiting list enabled, teams on the waiting list will not be shown (even when displaying `all` teams).<br>Allowed values are integer numbers (e.g. 64) or the value `all`. | `all` |
| **`sort`** | Sorting the table in ascending or descending order. The sort order is always according to the order of registration.<br>Allowed values are `asc` (for ascending order) and `desc` (for descending order). | `asc` |
| **`waitlist`** | Should the waiting list be displayed (instead of the regular table of registered teams)?<br>Allowed values are `false` and `true`. | `false` |
| **`country`** | Should a column for the country (displaying flags) be shown?<br>Allowed values are `false` and `true`. | `true` |
| **`club`** | Should a column for the sports club / city be shown?<br>Allowed values are `false` and `true` | `false` |
| **`registration-fee`** | Should a green dot be shown before a team name, if that team has paid the registration fee?<br>Allowed values are `false` and `true`. | `false` | 

#### Examples

1. Show all registered teams in order of registration (oldest registration first) for tournament with code name `EKC-test` (teams on waiting list are not shown):\
`[ekc-teams tournament="EKC-test"]`
2. Show the 5 most recently registered teams (most recent registration first, teams on waiting list are not shown):\
`[ekc-teams tournament="EKC-test" limit="5" sort="desc"]`
3. Show all registered teams on the waiting list in order of registration (oldest registration first):\
`[ekc-teams tournament="EKC-test" waitlist="true"]`
4. Show all registered teams in order of registration (oldest registration first), no column with country flags, but a column for sports club / city is shown, teams which have paid the registration fee are marked with a green dot:\
`[ekc-teams tournament="EKC-test" country="false" club="true" registration-fee="true"]`

### ekc-team-count

Allows to print the number of teams registered for a given tournament.
The output will only include active teams.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`tournament`** | Tournament code name (as defined in tournament registration). Mandatory. | none |
| **`max`** | If true: Returns the maximum allowed teams for this tournament.<br>If `false`: Returns the currently registered teams for this tournament.<br>Allowed values are `false` and `true`. | `false` |
| **`raw-number`** | Normally, shortcode output is formatted as html, suitable to include in html text etc.<br>A raw number is intended to be consumed by client-side javascript or similar (as for example in the EKC Counter widget).<br>If `true`: Returns a raw number<br>If `false`: Returns html formatted output<br>Allowed values are `false` and `true`. | `false` |

#### Examples

1. Output the number of registered teams for tournament with code name `EKC-test`:\
`[ekc-team-count tournament="EKC-test"]`
2. Output number of registered teams as raw number (can be used with the EKC Counter Widget):\
`[ekc-team-count tournament="EKC-test" raw-number="true"]`
3. Output the maximum allowed number of teams for tournament with code name `EKC-test`:\
`[ekc-team-count tournament="EKC-test" max="true"]`

### ekc-elimination-bracket

Prints the elimination bracket of a given tournament.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`tournament`** | Tournament code name (as defined in tournament registration). Mandatory. | none |
| **`country`** | Should the country flag for each team be shown?<br>Allowed values are `false` and `true`. | `true` |

#### Examples

1. Show the elimination bracket for tournament with code name `EKC-test`:\
`[ekc-elimination-bracket tournament="EKC-test"]`

### ekc-swiss-system

Allows to print the overall ranking and separate rounds (i.e. pairings of teams) of a swiss system tournament.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`tournament`** | Tournament code name (as defined in tournament registration). Mandatory. | none |
| **`ranking`** | Should the overall ranking be displayed (instead of separate rounds)?<br>Allowed values are `false` and `true`. | `false` |
| **`rounds`** | How many rounds should be displayed? If multiple rounds are output, higher rounds are output first. By default 2 rounds are shown, i.e. the current and last round.<br>Allowed values are integer numbers (e.g. 3) or the value `all`. | 2 |
| **`timer`** | Displays the current round and how many minutes are left, if a timer is started.<br>Example output:<br>Round 2: 3 minutes left.<br>Round 7 not started yet.<br>Allowed values are `false` and `true`. | `false` |
| **`country`** | Should a column for the country (displaying flags) be shown?<br>Allowed values are `false` and `true`. | `true` |

#### Examples

1. Show the current ranking table for tournament with code name `EKC-test`:\
`[ekc-swiss-system tournament="EKC-test" ranking="true"]`
2. Show results of the current and the last round (i.e. the two most current rounds):\
`[ekc-swiss-system tournament="EKC-test"]`
3. Show results of all rounds in descending order (most recent round first):\
`[ekc-swiss-system tournament="EKC-test" rounds="all"]`
4. Show results of the last 4 rounds in descending order:\
`[ekc-swiss-system tournament="EKC-test" rounds="4"]`
5. Show timer, i.e. the time left for the current round:\
`[ekc-swiss-system tournament="EKC-test" timer="true"]`

#### Auto Refresh

Pages with this shortcode support the URL-Parameter `refresh`. To display the current ranking on a screen and auto-refresh the page every 20 seconds, add the following to the page URL: `refresh=20`

Example: `http://example.tld/ekc-results?refresh=20`

### ekc-link

Allows building personalized pages for each team, reachable through a unique, shareable link.

Note: This shortcode will read the URL-Parameters `linkid` and `page_id`.
The unique `linkid` allows to resolve the tournament, team and all results.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`type`** | Show information that can be resolved by the URL-Parameter `linkid`.<br>Allowed values are `team-results`, `team-name` and `timer`.<br>`team-name`:<br>Simply prints the team name associated with the unique `linkid`<br>`timer`:<br>Shows the remaining time for the current round of the tournament. The tournament is resolved through the `linkid`<br>`team-results`:<br>Displays results of all rounds for the team associated with the unique `linkid`. Results for the current round can be reported and changed by the teams themselves | `team-results` |
| **`country`** | Should a column for the country (displaying flags) be shown?<br>Only relevant if `type="team-results"`<br>Allowed values are `false` and `true`. | `true` |

#### Examples

1. Show the name of the team associated with the URL-Parameter `linkid`:\
`[ekc-link type="team-name"]`
2. Show all results and opponents of the team associated with the URL-Parameter `linkid`:\
`[ekc-link type="team-results"]`
3. Show the remaining time of the current round. The tournament and the current round are resolved through the `linkid`:\
`[ekc-link type="timer"]`

### ekc-nation-trophy

An EKC European Kubb Champonships event consists of multiple separate tournaments, i.e. a 1vs1, 3vs3 and 6vs6 tournament.
From these tournaments an overall nation score is calculated.

#### Parameters

| Parameter | Description | Default Value |
| --------- | ----------- | ------------- |
| **`tournament-1vs1`** | Tournament code name (as defined in tournament registration) of the 1vs1 tournament. Mandatory. | none |
| **`tournament-3vs3`** | Tournament code name (as defined in tournament registration) of the 3vs3 tournament. Mandatory. | none |
| **`tournament-6vs6`** | Tournament code name (as defined in tournament registration) of the 6vs6 tournament. Mandatory. | none |

#### Examples

1. Show the nation trophy scores for an EKC event with three tournaments (1vs1, 3vs3, 6vs6):\
`[ekc-nation-trophy tournament-1vs1="EKC23-1" tournament-3vs3="EKC23-3" tournament-6vs6="EKC23-6"]`

## Roles and capabilities

Users with the _Administrator_ standard role have all EKC tournaments related capabilities.

The custom role _EKC Tournament Administrator_ has all EKC tournaments related capabilities as well as capabilities to create and edit pages.
Users with this role can see and edit detaild data of all tournaments (including tournaments of other users).

The custom role _EKC Tournament Director_ has EKC tournament related capabilities to create and run their own tournaments as well as to create and edit their own pages.
Users with this role can create and run their own tournaments, but cannot see detailed data or edit tournaments of other users.

### Roles

* Administrator (technical name `administrator`)
  - `ekc_read_tournaments`
  - `ekc_edit_tournaments`
  - `ekc_edit_others_tournaments`
  - `ekc_manage_tournaments`
  - `ekc_manage_others_tournaments`
  - `ekc_delete_tournaments`
  - `ekc_delete_others_tournaments`
  - `ekc_manage_backups`

* EKC Tournament Administrator (technical name `ekc_tournament_administrator`)
  - `ekc_read_tournaments`
  - `ekc_edit_tournaments`
  - `ekc_edit_others_tournaments`
  - `ekc_manage_tournaments`
  - `ekc_manage_others_tournaments`
  - `ekc_delete_tournaments`
  - `ekc_delete_others_tournaments`
  - `ekc_manage_backups`
  - `delete_others_pages`
  - `delete_pages`
  - `delete_private_pages`
  - `delete_published_pages`
  - `edit_others_pages`
  - `edit_pages`
  - `edit_private_pages`
  - `edit_published_pages`
  - `publish_pages`
  - `read`
  - `read_private_pages`
  - `upload_files`
  - `edit_theme_options`

* EKC Tournament Director (technical name `ekc_tournament_director`)
  - `ekc_read_tournaments`
  - `ekc_edit_tournaments`
  - `ekc_manage_tournaments`
  - `ekc_delete_tournaments`
  - `delete_pages`
  - `delete_private_pages`
  - `delete_published_pages`
  - `edit_pages`
  - `edit_private_pages`
  - `edit_published_pages`
  - `publish_pages`
  - `read`
  - `read_private_pages`
  - `upload_files`
  - `edit_theme_options`

### Capabilities

* `ekc_read_tournaments`
  - view all tournaments

* `ekc_edit_tournaments`
  - create new tournament
  - copy existing own tournament
  - edit own tournament
  - view teams of own tournaments
  - create teams for own tournaments
  - edit teams of own tournaments
  - teams csv export for own tournaments

* `ekc_edit_others_tournaments`
  - create new tournament
  - copy existing tournament
  - edit any tournaments
  - view teams of any tournament
  - create teams for any tournament
  - edit teams of any tournament
  - teams csv export for any tournament

* `ekc_manage_tournaments`
  - elimination bracket for own tournaments
  - swiss system for own tournaments
  - backup own tournaments
  - manage shareable links of own tournaments
  - view result logs of own tournaments

* `ekc_manage_others_tournaments`
  - elimination bracket for any tournament
  - swiss system for any tournament
  - backup any tournament
  - manage shareable links of any tournament
  - view result logs of any tournament

* `ekc_delete_tournaments`
  - delete own tournaments

* `ekc_delete_others_tournaments`
  - delete any tournament

* `ekc_manage_backups`
  - view backups
  - import backups

### Public Template Pages

The _EKC Tournament Director_ role cannot edit pages of other users.
But sometimes it is convenient to define template pages, which contain sample usages of shortcodes and can be duplicated to get started quickly with a new tournament page.
_Public template pages_ can be edited by all users, even if they are not the author of the page, and they dont have the `edit_others_pages` capability.
A regular page becomes a _public template page_ by adding the custom field `ekc-public-template` with a value of `true`.

The EKC Tournament Manager plugin comes with a feature to duplicate existing pages, which helps to quickly setup a new tournament based on an existing tournament page or a template page.
When creating a copy of a page, the `ekc-public-template` custom field will _not_ be copied to the new page.

## Contact Form 7 Integration

Registration forms built with the popular WordPress plugin [Contact Form 7](https://contactform7.com) can be stored directly to the EKC Tournament Manager database.

The following attributes are recognized and can be used within a contact form definition:

| Attribute | Description |
| --------- | ----------- |
| `[ekc-tournament]` | Code name of a tournament, used to identify an existing tournament. Usually defined as a hidden form input and passed as a shortcode attribute to the form. |
| `[ekc-active]` | Boolean value (true/false) indicating whether a registered team is active or inactive by default. Usually defined as a hidden form input and passed as a shortcode attribute to the form. |
| `[ekc-waitlist]` | Boolean value (true/false) indicating whether a registered team is put on the waiting list. Usually defined as a hidden form input and passed as a shortcode attribute to the form. |
| `[ekc-teamname]` | Team name. Usually defined as mandatory form input for team tournaments. |
| `[ekc-firstname1]`<br>`[ekc-lastname1]`<br>`[ekc-firstname2]`<br>`[ekc-lastname2]`<br>`[ekc-firstname3]`<br>`[ekc-lastname3]`<br>`[ekc-firstname4]`<br>`[ekc-lastname4]`<br>`[ekc-firstname5]`<br>`[ekc-lastname5]`<br>`[ekc-firstname6]`<br>`[ekc-lastname6]` | First name and last name of players 1 to 6. Optional form inputs for team tournaments. |
| `[ekc-firstname]`<br>`[ekc-lastname]` | First name and last name of a player. Usually defined as mandatory form inputs for a 1vs1 tournament. |
| `[ekc-email]` | E-mail address of a team or player. Usually defined as mandatory form input. |
| `[ekc-phone]` | Phone number of a team or player. |
| `[ekc-country]` | 2 letter ISO code of the country of a team or player. Examples: `ch`, `be`, `de`, `se`, `cz`. |
| `[ekc-club]` | City or club of a team or player. |

### Example Configuration of a Contact Form

Team name and e-mail address are mandatory form inputs.
Tournament code name, active and waitlist attributes will be passed as shortcode attributes.

```
<label> Team name
	[text* ekc-teamname] </label>

<label> E-mail
	[email* ekc-email] </label>

[hidden ekc-tournament default:shortcode_attr]

[hidden ekc-active default:shortcode_attr]

[hidden ekc-waitlist default:shortcode_attr]

[submit "Register team"]
```

Embedding the registration form through a shortcode in a WordPress page:

```
[contact-form-7 id="123" title="Registration Form" ekc-tournament="EKC-test" ekc-active="true" ekc-waitlist="false"]
```