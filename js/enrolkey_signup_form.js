/* we fill fields 'firstname' and 'lastname' automatically with dummy data
	if the User hasn't completed them, just when he clicks on "submit":
   "firstname" for field 'firstname', and "lastname" for field 'lastname'
   (this is done to avoid breaking moodle since firstname and lastname 
   are normally required.) */

var mform = document.getElementsByClassName('mform')[0];

function default_names(){
	var firstname = document.getElementById('id_firstname');
	var lastname = document.getElementById('id_lastname');
	// set the default values if blank
	if (firstname.value == '' || firstname.value == null){
		firstname.value = 'firstname';
	}
	if (lastname.value == '' || lastname.value == null){
		lastname.value = 'lastname';
	}
};

if(mform.addEventListener){
    mform.addEventListener("submit", default_names, false); 
}
