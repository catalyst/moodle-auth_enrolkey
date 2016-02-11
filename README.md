Moodle Token auth plugin
========================

This is a functional clone of the Email-based self-registration plugin that also provides additionally functionality asking for a token. When a user enters a valid token it will automatically enrol them into the course that token was specified for.

Courses that provide self enrolment can restrict access to them with a key. If the signup token matches any course enrolment key then the new user will be enrolled into those courses. 

# Installation

Add the plugin into /auth/token/

Run the Moodle upgrade.

# Setup
Enable the Token authentication plugin. 
    `(Site administration > Plugins > Authentication > Manage Authentication)`
    
Enable the Self enrolment plugin. 
    `(Site administration > Plugins > Enrolments > Manage enrol plugins)`

# Usage
Enable a new self enrolment instance in the course requried. 
    `(Course administration > Users > Enrolment methods > Add method > Self enrolment)`

The Enrolment key field will be used for the automatic token enrolment on signup.