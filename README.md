[![Build Status](https://travis-ci.org/catalyst/moodle-auth_enrolkey.svg?branch=master)](https://travis-ci.org/catalyst/moodle-auth_enrolkey)

# Moodle Enrolment key based self-registration

This auth plugin combines the best of both email based signup and self enrolment keys into a streamlined process making it much faster for students to get into a course. For the student it saves around 9-10 clicks and avoids context switching between a browser and their email client where they can become easily become disengaged or run into issues if their email is unavailable.

This is mostly a clone of the Email-based self-registration plugin that also enrols a user into available courses based on a token supplied. When a user enters a valid token it will automatically enrol them into the course that token was specified for, and then take them directly to that course. Also an optional bonus setting: because they have demonstrated knowledge of a secret code we know they are a real human so we let them straight in without forcing them to confirm their email. For some use cases where they may login and complete their course in a single session we may not ever care about their email being valid.

Courses that provide self enrolment can restrict access to them with a key. If the signup token matches any course enrolment key then the new user will be enrolled into those courses. 

Found in the Moodle plugins directory at [https://moodle.org/plugins/auth_enrolkey](https://moodle.org/plugins/auth_enrolkey)


* [Branches](#branches)
* [Installation](#installation)
* [Setup](#setup)
* [Settings](#settings)
* [Admin Usage](#admin-usage)
* [Client Usage](#client-usage)
* [TODO](#todo)
* [Support](#support)


Branches
--------

For all current moodle installations, use the master branch.

Installation
------------ 

Add the plugin to /auth/enrolkey/

Run the Moodle upgrade.

Setup
-----
First enable the Enrolment key based self-registration plugin for use.

`Site administration > Plugins > Authentication > Manage Authentication`

On the same page that you manage authentication options, scroll down to the common settings and select `Enrolment key based self-registration` in the Self Registration drop down list for `registerauth`.
    
Enable the Self enrolment plugin.

`Site administration > Plugins > Enrolments > Manage enrol plugins`
    
Settings
--------

It is possible to force the the enrolment key as a required element for signing up.

Admin Usage
-----------

## Course enrolment keys

Enable a new Self enrolment method in the course required. 

`Course administration > Users > Enrolment methods > Add method > Self enrolment`

The Enrolment key field that is visible will be used for the automatic enrolment on signup.

When creating new Self enrolment method, provide a custom instance name that is descriptive enough to determine how and when this specific instance will be used for enrolling.

Please add additional Self enrolment methods for the course that you require automatic enrolment access for.

## Group enrolment keys

If you select `Use group enrolment keys: Yes` during creating of a new Self enrolment method, it enables a functionality of automatic adding self enrolled students to the groups created in the course.

In order for that to work, create a group in the course and specify `Enrolment key`. 

Please note, that this key must be different from the course enrollment key which you set in the course Self enrolment method.

As a result, students will be able to subscribe using the group enrolment key. That will enrol them to the course where the group was created and also will add them to that group.

Client Usage
------------

When a user tries to log in they are given the option to create an account.

The user can enter a token into the field name `Enrolment key`. 

There is an optional setting that can force this field to be required.

If the token matches **any** valid instance of self enrolment, then the user will be enrolled to those courses.  

TODO
----

Add option to bypass view.php, this may not be required if only enroling into one course.

Support
-------
For any issue with the plugin, please log the in the github repository here:

https://github.com/catalyst/moodle-auth_enrolkey/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us



This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<a href="https://www.catalyst-au.net/"><img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400"></a>

