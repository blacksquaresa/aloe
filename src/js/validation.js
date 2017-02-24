//**************************JAVASCRIPT FORM VALIDATION MODULE**************************//
// This module can be used for basic client side form validations in JavaScript.
// 
// Date Created: 25 February 2003
// Last Update: 3 March 2003
// Tested for: Netscape 6.1 and Internet Explorer 5.5 and above
// Author: Benjé Mouton
// Company: 001 Digital Construction
// Files: "validation.asp"
//
//********************************STANDARD SCRIPT BLOCK********************************//
// Include the following script block in the head tag in order for the functions to
// work.
//
// <script language=javascript>
// <!--
// 	function validate(sForm) {
// 		ErrorMsg = "";
//
// 		||<<OOoo Call Functions Here ooOO>>||
//
// 		if (ErrorMsg != "") {
// 			ErrorMsg = "The form could not be submitted for the following reasons:" + ErrorMsg;
// 			window.alert(ErrorMsg);
// 			return false;
// 		}
// 	}
// //-->
// </script>
// <script language="javascript" src="validation.asp"></script>
//
//************************************EVENT HANDLER************************************//
// Include the following event handler to activate the form validation functions.
//
// onSubmit/onClick="return(validate(this.form.name))"
//
//*************************************************************************************//
// This module contains the following functions:										
//		non_blank(form's name, field's name, error message);	
//		non_blank_set(form's name, [field names], error message);							* Validates for blank fields
//		is_equal(form's name, first field's name, second field's name, error message);	* Checks if given fields are equal
//		valid_email(form's name, field's name, error message);							* Validates an email address
//		credit_card(form's name, field's name, sMsg);									* Validates credit card numbers
//		format_currency(form's name, field's name, error message);						* Checks and formats to a currency, returns 2 decimals
//		check_checked(form's name, field's name, minimum selection, error message);		* Checks if checkboxes are checked
//		check_selected(form's name, field's name, minimum selection, error message);	* Checks if multiple selections are made
//		check_ext(sForm, sField, sMsg);													* Checks and allows on certain files
//		option_selected(form's name, field's name, minimum selection, error message);	* Checks if radio buttons are selected
//		is_integer(form's name, field's name, error message);							* Validates integer values
//		is_float(form's name, field's name, error message);								* Validates floating point values
//		decimal(value, decimal places);													* Returns a formatted decimal value
//		
// <script language="javascript">

//***********************************BASIC FUNCTIONS***********************************//
var ErrorMsg = "";
var Elements = new Array();

function non_blank(sForm, sField, sMsg) {												
// SYNTAX: non_blank(form's name, field's name, error message);
	var objControl = document[sForm][sField];

	if (trim_string(objControl.value).length < 1) {
		if (sMsg == "") {
			return false;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}

	} else {
		return true;
	}
}

function non_blank_set(sForm, aFields, sMsg) {												
// SYNTAX: non_blank_set(form's name, [field one, field two], error message);
	var found = false;
	for(i=0;i<aFields.length;i++){
		var objControl = document[sForm][aFields[i]];
		if (trim_string(objControl.value).length >= 1){
			found = true;
			break;
		}
	}

	if (!found) {
		if (sMsg == "") {
			return false;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}

	} else {
		return true;
	}
}

function is_equal(sForm, sField1, sField2, sMsg, bRequired) {							
// SYNTAX: is_equal(form's name, first field's name, second field's name, error message);
	var objControl1 = document[sForm][sField1];
	var objControl2 = document[sForm][sField2];
	var bContents1 = non_blank(sForm, sField1, "");
	var bContents2 = non_blank(sForm, sField2, "");
	var bEqual = 0;

	if (trim_string(objControl1.value) == trim_string(objControl2.value) && bContents1 && bContents2) {
		bEqual = true;
	} else {
		bEqual = false;
	}

	if (bRequired && !bEqual) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	} else if (!bRequired && !bEqual) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function valid_email(sForm, sField, sMsg, bRequired) {									
// SYNTAX: valid_email(form's name, field's name, error message);
	var objControl = document[sForm][sField];
	var bContents = non_blank(sForm, sField, "");
	var bCheck = 0;

	if (objControl.value.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) != -1) {
		bCheck = true;
	} else {
		bCheck = false;
	}
	
	if (bRequired && !bContents && !bCheck) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	} else if (bRequired && bContents && !bCheck) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	} else if (!bRequired && bContents && !bCheck) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function valid_phone(sForm, sField, sMsg, bRequired) {												
// SYNTAX: valid_phone(form's name, field's name, error message);
	var objControl = document[sForm][sField];
	var testString = /^([\d\- ]){7,}$/;
	var bContents = non_blank(sForm, sField, "");
	
	var bCheck = testString.test(objControl.value);
	
	if ((bRequired && !bCheck)||(!bRequired && bContents && !bCheck)) {
		if (trim_string(sMsg).length < 1) {
			return false;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}
	}  else {
		return true;
	}
}


function check_checked(sForm, sField, iMin, sMsg) {										
// SYNTAX: check_checked(form's name, field's name, minimum selection, error message);
	var elements = document.getElementsByName(sField);
	var intChecked = 0;

	for (var i = 0; i < elements.length; i++) {
		if (elements[i].checked) {
			intChecked++;
		}
	}
	
	if (intChecked < iMin) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function check_selected(sForm, sField, iMin, sMsg) {									
// SYNTAX: check_selected(form's name, field's name, minimum selection, error message);
	var objControl = document[sForm][sField];
	var intSelected = 0;

	for (var i = 0; i < objControl.length; i++) {
		if (objControl[i].selected) {
			intSelected++;
		}
	}

	if (intSelected < iMin) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function option_selected(sForm, sField, iMin, sMsg) {									
// SYNTAX: option_selected(form's name, field's name, minimum selection, error message);
	var elements = document.getElementsByName(sField);
	var intChecked = 0;

	for (var i = 0; i < elements.length; i++) {
		if (elements[i].checked) {
			intChecked++;
		}
	}
	
	if (intChecked < iMin) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function check_selected_value(sForm, sField, sMsg) {									
// SYNTAX: check_selected_value(form's name, field's name, error message);
	var objControl = document[sForm][sField];

	if (objControl.value == "") {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	}
}

function check_ext(sForm, sField, sMsg) {
	var objControl = document[sForm][sField];

	if (objControl.value != "") {
		if (objControl.value.search(/(\.jpg|\.jpeg|\.wmf|\.gif)/gi) != -1) {
			return true;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}
	} else {
		return true;
	}
}


function check_resource_ext(sForm, sField, sMsg) {
	var objControl = document[sForm][sField];

	if (objControl.value != "") {
		if (objControl.value.search(/(\.pdf|\.doc|\.xls|\.ppt|\.jpg|\.jpeg|\.gif)/gi) != -1) {
			return true;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}
	} else {
		return true;
	}
}

//***********************************DATE FUNCTIONS************************************//

function valid_date(sForm, sField, sMsg) {												
// SYNTAX: valid_date(form's name, field's name, errormessage);
	var objControl = document[sForm][sField];
	var iDays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
	var sDate = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	var Err = false;
	
	var tDate = objControl.value.split("/");

	var day = tDate[0];
	var month = tDate[1];
	var year = tDate[2];

	if (!day || !month || !year) {
		Err = true;
	}	
	
	if (year.length != 4 && year.length != 2 && !Err) {
		Err = true;
	}	

	if (year % 4 == 0 && year % 400 == 0 && !Err) {
		iDays[1] = 29;
	}
	
	if (day > iDays[eval(month - 1)] && !Err) {
		Err = true;
	}
	
	if (Err) {
		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
	} else {
		objControl.value = day + "/" + month + "/" + year;
		return true;
	}
}

//***********************************MATHS FUNCTIONS***********************************//

function is_integer(sForm, sField, sMsg) {												
// SYNTAX: is_integer(form's name, field's name, error message);
	var objControl = document[sForm][sField];
	var testString = /\D/;
	
	if (testString.test(objControl.value))	{
		if (trim_string(sMsg).length < 1) {
			return false;
		} else {
			ErrorMsg = ErrorMsg + "\n  -  " + sMsg;
		}
	} else {
		return true;
	}
}

function is_float(sForm, sField, sMsg, bRequired) {												
// SYNTAX: is_float(form's name, field's name, error message);
	var objControl = document[sForm][sField];
	var bFloat = new Boolean(parseFloat(trim_string(objControl.value)) == trim_string(objControl.value));
      var ShowMsg = new Boolean(sMsg.length > 1);
      var bContents = new Boolean(trim_string(objControl.value).length > 1);
      
      if (bRequired == true && bFloat == false) {
      	if (ShowMsg == true) {
      		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;	
      	} else {
      		return false;
      	}
      } else if (bRequired != true && bContents == true && bFloat == false) {
      	if (ShowMsg == true) {
      		ErrorMsg = ErrorMsg + "\n  -  " + sMsg;	
      	} else {
      		return false;
      	}
      } else {
      	return true;
      }
}

function decimal(sValue, iDec) {
      if (sValue.length > 0) {														
      // SYNTAX: decimal(value, decimal places);
            var sTemp = Math.round(Math.pow(10, iDec) * sValue) + "";
            var sDecimal = sTemp.substring(0, sTemp.length - iDec) + "." + sTemp.substring(sTemp.length - iDec, sTemp.length);
            return sDecimal;
      } else {
            return "";
      }
}

//**********************************STRING FUNCTIONS***********************************//

function trim_string(string) {
	if (string.length != "") {
		return string.replace(/\s+/g, "");
	} else {
		return string;
	}
}

//*********************************CONFIRMATION POPUP**********************************//

function click_confirm(sElement, sMsg, bEnabled) {
	
}

//limit field characters
function limitText(limitField, limitNum,inputChar,maximumNote,limitcount) {
var limitField = document.getElementById(limitField);
var inputChar = document.getElementById(inputChar);
var maximumNote = document.getElementById(maximumNote);
var limitCountDown = document.getElementById(limitcount);
limitText.cache[limitField+'classname'] = limitText.cache[limitField+'classname'] || inputChar.className;

	if (limitField.value.length > limitNum) {
		inputChar.value = inputChar.value.substring(0,limitNum);
	    inputChar.className = limitText.cache[limitField+'classname']+" borderred";  
	    maximumNote.style.color = "red";  
	   
		}else if (limitField.value.length <= limitNum) {
	    inputChar.className = limitText.cache[limitField+'classname'];
	    maximumNote.style.color ='';
	    limitCountDown.innerHTML = limitNum-inputChar.value.length+' left';
		}
}
limitText.cache = {};
// </script>