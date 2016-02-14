Moodle Token auth plugin
========================

This is a functional clone of the Email-based self-registration plugin that also enrols a user into available courses based on a token supplied. When a user enters a valid token it will automatically enrol them into the course that token was specified for.

Courses that provide self enrolment can restrict access to them with a key. If the signup token matches any course enrolment key then the new user will be enrolled into those courses. 

# Installation

Add the plugin into /auth/token/

Run the Moodle upgrade.

# Setup
Enable the Token authentication plugin. 
    `(Site administration > Plugins > Authentication > Manage Authentication)`

On the same page to manage authentication options, scroll down to the common settings and select `Token Authentication` in the Self Registration drop down list for `registerauth`.
    
Enable the Self enrolment plugin. 
    `(Site administration > Plugins > Enrolments > Manage enrol plugins)`
    
# Settings

It is possible to force the requirement for a token to be entered.

# Usage (admin)

Enable a new self enrolment instance in the course required. 
    `(Course administration > Users > Enrolment methods > Add method > Self enrolment)`

The Enrolment key field that is visible will be used for the automatic token enrolment on signup.

When creating new instances of the Self enrolment method, provide a custom instance name that is descriptive enough to determine how and when this specific instance will be used for enrolling.

Please add additional enrolment instances for the subjects that you require automatic token access for.

# Usage (client)

When a user tries to log in they are given the option to create an account.

The user can enter a token into the field name `Course token`. 

There is an optional setting that can force this field to be required.

If the token matches **any** valid instance of self enrolment, then the user will be enrolled to those courses.  