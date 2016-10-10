[![Build Status](https://travis-ci.org/catalyst/moodle-auth_enrolkey.svg?branch=master)](https://travis-ci.org/catalyst/moodle-auth_enrolkey)

Moodle Enrolment key based self-registration
========================

This is a functional clone of the Email-based self-registration plugin that also enrols a user into available courses based on a token supplied. When a user enters a valid token it will automatically enrol them into the course that token was specified for.

Courses that provide self enrolment can restrict access to them with a key. If the signup token matches any course enrolment key then the new user will be enrolled into those courses. 

Found in the Moodle plugins directory at [https://moodle.org/plugins/auth_enrolkey](https://moodle.org/plugins/auth_enrolkey)

# Installation

Add the plugin to /auth/enrolkey/

Run the Moodle upgrade.

# Setup
First enable the Enrolment key based self-registration plugin for use.
    `(Site administration > Plugins > Authentication > Manage Authentication)`

On the same page that you manage authentication options, scroll down to the common settings and select `Enrolment key based self-registration` in the Self Registration drop down list for `registerauth`.
    
Enable the Self enrolment plugin. 
    `(Site administration > Plugins > Enrolments > Manage enrol plugins)`
    
# Settings

It is possible to force the the enrolment key as a required element for signing up.

# Usage (admin)

## Course enrolment keys

Enable a new Self enrolment method in the course required. 
    `(Course administration > Users > Enrolment methods > Add method > Self enrolment)`

The Enrolment key field that is visible will be used for the automatic enrolment on signup.

When creating new Self enrolment method, provide a custom instance name that is descriptive enough to determine how and when this specific instance will be used for enrolling.

Please add additional Self enrolment methods for the course that you require automatic enrolment access for.

## Group enrolment keys

If you select `Use group enrolment keys: Yes` during creating of a new Self enrolment method, it enables a functionality of automatic adding self enrolled students to the groups created in the course.

In order for that to work, create a group in the course and specify `Enrolment key`. 

Please note, that this key must be different from the course enrollment key which you set in the course Self enrolment method.

As a result, students will be able to subscribe using the group enrolment key. That will enrol them to the course where the group was created and also will add them to that group.

# Usage (client)

When a user tries to log in they are given the option to create an account.

The user can enter a token into the field name `Enrolment key`. 

There is an optional setting that can force this field to be required.

If the token matches **any** valid instance of self enrolment, then the user will be enrolled to those courses.  

# TODO

Add option to bypass view.php, this may not be required if only enroling into one course.

