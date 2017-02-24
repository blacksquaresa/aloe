function CMEnquiry_sendMail(id){
	var formelem = document.getElementById('CMEnquiry_form_'+id);
	var workingelem = document.getElementById('CMEnquiry_working_'+id);
	var mod = "CMEnquiry_";
	var name = document.getElementById(mod+'name_'+id);
	var email = document.getElementById(mod+'email_'+id);
	var phone = document.getElementById(mod+'phone_'+id);
	var enquiry = document.getElementById(mod+'enquiry_'+id);
	var question = document.getElementById(mod+'question_'+id);
	var answer = document.getElementById(mod+'answer_'+id);
	
	name = name? name.value:'';
	email = email? email.value:'';
	phone = phone? phone.value:'';
	enquiry = enquiry? enquiry.value:'';
	question = question? question.value:'';
	answer = answer? answer.value:'';
	
	ErrorMsg="";
	//custom checks	
	non_blank(formelem.name,mod+'name_'+id,'Please enter your name.');
	valid_email(formelem.name,mod+'email_'+id,'Please enter a valid email address.');
	non_blank_set(formelem.name,[mod+'email_'+id,mod+'phone_'+id],'Please enter either an email address or contact number, so we can get back to you.');	
	if(ErrorMsg==''){
		var check = agent.call("/content/CMEnquiry/CMEnquiry.ajax.php","CMEnquiry_checkCaptcha","",question,answer);
		if((!!check)===false) ErrorMsg = "\n\r  - Please answer the anti-spam question correctly.";
	}
	
	if(ErrorMsg==''){
		formelem.style.display = 'none';
		workingelem.style.display = 'block';
		var res = agent.call("/content/CMEnquiry/CMEnquiry.ajax.php","CMEnquiry_sendForm",'',id,name,email,phone,enquiry);
		if(res=='success')
			workingelem.innerHTML = '<h3>Thank You</h3><p>Your enquiry has been submitted, and we will get back to you shortly.</p>';
		else{
			alert(res);
			formelem.style.display = 'block';
			workingelem.style.display = 'none';
		}
		
	}else{
		alert("please fix the following errors: "+ErrorMsg);
	}
}