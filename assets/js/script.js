$(function(){
	
	stashes = {};
	var tableval,aggregationval,cggregationval,frequencyval,variableval,periodval;

	//Initialize chosen plugin
	try{
		$('.chosentable').chosen();
	}catch(Exception)
	{
		console.log(Exception);
	}
	
	//Initialize TinyMce
	try{
		tinymce.init({ selector:'.database_help' });
	}catch(Exception)
	{
		console.log(Exception);
	}

	initdatepicker();

	$('.numlock').bind('keyup',function(e){
			var present = $(this).val();
			if(!$.isNumeric(present) && present.length > 0)
			{
					alert('Only number allowed!');
			}
	});

	//Change request status
	$(document).on('change','.requeststatus', function(){
		$(this).closest('form').submit();
	});

	//Check if any database has been selected 
	databaseSelected = false;
	
	periodisset      = false;
	
	fieldisset       = false;

	requests = [];

	//Retrieve Database List
	// $(document).on('click','.databaselist', function()

	//Trigger old DB clicked from previous request
	cookieid = getCookie('databaseClickedFromSurvey');
	clickADatabase(cookieid);

	$(document).on('click','.databaselist', function(){

		path = window.location.pathname.replace("/","");
		lastid = $(this).attr('id');

		//If database was selected from survey page 
		//redirect back to home then 
		//click last database triggered by user
		//from previous request
		if(path == "surveyresearch")
		{
			setCookie('databaseClickedFromSurvey',lastid, 1);

			window.location.assign('/');
		}
		else{
			setCookie('databaseClickedFromSurvey','',-1);
		}

		var generalclass  = 'dart fa fa-caret-right databaselist',
			selectedclass = 'check fa fa-check databaselist',
			type          = $(this).data('type'),
			url           = $(this).data('fetch'),
			id            = "#tables",
			data          =  {},
			divs 		  =  ['#tables','#laggregation','#caggregation','#frequency','#variables','#period'],
			databasename  = $(this).data('code');

		//Set same class for all 
		$('.databaselist').map(function(database){
			$(this).attr('class',generalclass).css('color','initial');
		});

		//Clear succesive divs
		for(selector in divs)
		{
			$(divs[selector]).html('');
		}

		//Excempt clicked one
		$(this).attr('class',selectedclass).css('color','rgba(228, 79, 79, 0.8)');

		//Hide empty notification
		$('#empty').addClass('hide');

		//Display loader notification
		displayloader();

		setTimeout(loadpage(url,id),3000);

		fieldisset = false;

		//Add to overview
		$('#databaseoverview').removeClass('hide').children('span.content').html(databasename);

		$('input[name=databasename]').val(databasename);

		$('#submitdiv').addClass('hide');

		//Display reviewsave
		savereview(false);

		return false;
	});

	/**
	* Retrieve aggregation List
	*/
	$(document).on('change','select[name=table]',function(){
		var url       = 'laggregation/' + $(this).find(':selected').data('id'),
		id            = "#laggregation",
		data          =  {},
		divs 		  =  ['#laggregation','#caggregation','#frequency','#variables','#period'],
		datainput 	  =  $('select[name=table]').val();

		// console.log(url);
		// return false;

		//Clear succesive divs
		for(selector in divs)
		{
			$(divs[selector]).html('');
		}

		//Display loader notification
		displayloader();

		setTimeout(loadpage(url,id),3000);

		fieldisset = false;

		//Overview
		overview(datainput,'.dtable');

		$('#submitdiv').addClass('hide');

		//Display reviewsave
		savereview(false);

		return false;
	});

	/**
	* Retrieve category aggregation List
	*/
	$(document).on('change','select[name=laggregation]',function(){
		var url       = 'caggregation/' + $(this).find(':selected').data('id'),
		id            = "#caggregation",
		data          =  {},
		divs 		  =  ['#caggregation','#frequency','#variables','#period'],
		datainput 	  =  $('select[name=laggregation]').val();

		//Clear succesive divs
		for(selector in divs)
		{
			$(divs[selector]).html('');
		}

		//Display loader notification
		displayloader();

		setTimeout(loadpage(url,id),3000);

		fieldisset = false;

		//Overview
		overview(datainput,'.dlaggregation');

		$('#submitdiv').addClass('hide');

		//Display reviewsave
		savereview(false);

		return false;
	});

	/**
	* Retrieve frequency
	*/
	$(document).on('change','select[name="caggregation[]"]',function(){
		var url       = 'frequency/' + $('select[name=table]').find(':selected').data('id'),
		id            = "#frequency",
		data          =  {},
		divs 		  =  ['#period'],
		datainput 	  =  $('select[name="caggregation[]"]').val();

		console.log("category added here");

		//Display loader notification
		displayloader();
		setTimeout(loadpage(url,id),3000);

		fieldisset = false;

		//Overview
		overview(datainput,'.dcaggregation');

		$('#submitdiv').addClass('hide');

		//Display reviewsave
		savereview(false);

		return false;
	});

	/**
	* Retrieve variables
	*/
	$(document).on('change','select[name=frequency]',function(){
		var url       = 'variable/' + $('select[name=table]').find(':selected').data('id') + '/' + $('select[name=laggregation]').find(':selected').data('id') + '/' + $('select[name=frequency]').find(':selected').data('id'),
		id            = "#variables",
		url2          = 'periods/' + $('select[name=frequency]').find(':selected').data('id')
		id2           = "#period",
		data          =  {},
		divs 		  =  ['.null'],
		datainput 	  =  $('select[name=frequency]').val();

		//Clear succesive divs
		for(selector in divs)
		{
			$(divs[selector]).html('');
		}

		//Display loader notification
		displayloader();

		setTimeout(function(){loadpage(url,id); loadpage(url2,id2) },3000);

		fieldisset = false;

		//Overview
		overview(datainput,'.dfrequency');
		

		$('#submitdiv').addClass('hide');

		//Display reviewsave
		savereview(false);

		return false;
	});

	/**
	* Retrieve periods
	*/
	$(document).on('change',".dvariable",function(){

		//Allow generating of period if variables is not empty
		if($('.dvariable').val() == null)
		{
			// $('#period').html('');
			$('#submitdiv').addClass('hide');

			return false;
		}

		var url       = 'periods/' + $('select[name=frequency]').find(':selected').data('id'),
		id            = "#period",
		data          =  {},
		// divs 		  =  ['#period'],
		datainput 	  =  $(".dvariable").val();

		//Overview
		overview(datainput,'.dvariable');

		//Display reviewsave
		savereview(false);

		return false;
	});

	// Display 
	$(document).on('change blur','input[name=to]',function(){
		if($(this).val() == null)
		{
			$('#submitdiv').addClass('hide');

			return false;
		}

		$('#submitdiv').removeClass('hide');

		fieldisset = true;

		var datainput 	  =  $(".dperiod").val();

		//Overview
		overview(datainput,'.dperiod');

		//Display reviewsave
		savereview(true);

		return false;
	});

	// Display comment in overview
	$(document).bind('keyup','textarea[name=comment]',function(){

		var datainput 	  =  $('textarea[name=comment]').val();

		//Overview
		overview(datainput,'.dcomment');

		//Display reviewsave
		savereview(true);

		return false;
	});

	// Display comment in overview
	$(document).bind('blur','textarea[name=comment]',function(){

		var datainput 	  =  $('textarea[name=comment]').val();

		//Overview
		overview(datainput,'.dcomment');

		//Display reviewsave
		savereview(true);

		return false;
	});

	/**
	*Begin Ajax Request
	*
	*/
	$('.ajaxForm').on('submit',function(e){

		e.preventDefault();

		//show notification
		$('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Submitting Entries Please wait....');

		var form = $(this),
			url  = form.attr('action'),
			data = form.serialize();

			//Perform Ajax Post Request
			$.post(url,data)

			.done(function(response){

				//Save selected
				tableval       = $('select[name=table]').val(),
				aggregationval = $('select[name=laggregation]').val(),
				cggregationval = $('select[name="caggregation[]"]').val(),
				frequencyval   = $('select[name=frequency]').val(),
				variableval    = $('select[name="variable[]"]').val(),
				periodval      = $('select[name="period[]"]').val();
				toval      	   = $('input[name=to]').val();
				fromval        = $('input[name=from]').val();
				commentval     = $('textarea[name=comment]').val();

				datas = {};
				datas = {"tableval" : tableval,'aggregationval' : aggregationval,
						'cggregationval' : cggregationval,'frequencyval' : frequencyval,
						'variableval' : variableval,"periodval" : periodval,"toval" : toval,"fromval" : fromval,"commentval" : commentval};

				// console.log(datas);

				//When submission proposed we clone the form to use as an edit
				cloned = $(document).find('form.ajaxForm').clone();

				createEditForm(cloned,response,datas);			//Here we pass the cloned form and reponse id from server to be futher manipulated by a function

				var submissiontype = $('#submissiontype').val();
				

				//Line up requests
				requests.push(response);

			var requestdetail = '<div class="col-md-12" id="requestoverviewof' + response + '" style="padding: 0px;"><h5 class="site-init" style="display: inline-block;font-size: 16px;">';
				requestdetail += 'Request ';
				requestdetail += requests.length;
				requestdetail += '</h5><div class="col-md-12 nopad"><button onclick="printView(\'printrequest?overview='; 
				requestdetail += response;
				requestdetail += '\')" class="btn btn-sm  button-custom btn-custom-two">Print</button>';
				requestdetail += '<a href="printrequest?overview='; 
				requestdetail += response;
				requestdetail += '" target="_blank" class="btn btn-sm  button-custom btn-custom-two">View</a>';
				requestdetail += '<a onclick="editwindow('; 
				requestdetail += response;
				requestdetail += ')"  class="btn btn-sm  button-custom btn-custom-two" title="Edit request"><span class="ion-edit"></span></a>';
				requestdetail += '<a onclick="deleteRequest(';
				requestdetail += response; 
				requestdetail += ')"  class="btn btn-sm  button-custom btn-custom-two" title="Remove"><span class="ion-ios-trash"></span></a></div><hr></div>';

				$('#requestlists').prepend(requestdetail);

				var all   = ['#databaseoverview','#tableoverview','#lagoverview','#cagoverview','#freqoverview','#variableoverview','#periodoverview','#commentoverview'];

				//Clear overview div
				for (selector in all) {
					$(all[selector]).addClass('hide').children('span.content').html('');
				};

				//Clear select fields
				var field = ['#tables','#laggregation','#caggregation','#frequency','#variables','#period'],
					generalclass  = 'dart fa fa-caret-right databaselist';

				//Hide submit div
				$('#empty').addClass('hide');
				$('#submitdiv').addClass('hide');
				$('.dcomment').val('');

				//Clear and hide additional date div if any
				$('#manualperiod').addClass('hide');
				$('input[id=from]').val('');
				$('input[id=to]').val('');


				for (selector in field) {
					$(field[selector]).html('');
				};

				//Clear database select
				//Set same class for all 
				$('.databaselist').map(function(database){
					$(this).attr('class',generalclass).css('color','initial');
				});

				//Clear database field
				$('input[name=databasename]').val('');

				//Notification to continue
				$('#empty').removeClass('hide');
				$('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Proceed To Making More Requests');

				//Once there are no more requests to add fill client form
				if(submissiontype == "nomore")
				{
					submitAllRequest()
				}
				
				//Show button to allow to submit all request without adding more
				savereview(true);

				return false;
			})
			.fail(function(){
				console.log("Unable to submit request");
			});

			return false;

	});
	/*
	*End Ajax request
	*
	*/

	$('.notify').fadeOut(9000);

	/**
	*Toggle Client Type Details
	**/
	$('select[name=clienttype]').bind('click',function(){
		if($(this).val() == "affiliate")
		{
			$('.institution_detail').removeClass("hide");
			$('.clientsubmit').addClass("hide");								//Hide submit button until code match
		}else{
			$('.institution_detail').addClass("hide");
			$('.clientsubmit').removeClass("hide");								//Remove hide from submit button since its not an affiliate client
		}
	});

	//Check if instituition code match then we can enable submission of form else don't
	$(document).on('keyup','input[name=instituition_code]',function(){

		code = $('[name=instituition]').find(':selected').data('code');
					
		if(code != $(this).val() && $(this).val().length == code.length || $(this).val().length > code.length) showarning('<b class="site-red">Warning:</b> Invalid institution code provided');

		if(code == $(this).val())
		{
			$('.codeinfo').html('<i class="fonts ion-ios-checkmark green"></i> Valid code supplied');
			$('.clientsubmit').removeClass("hide");		//If code match allow submit
		} 

		if(code != $(this).val())
		{
			info = 'Provide correct institution code to activate submit button';
			$('.codeinfo').html(info).removeClass('hide');
			$('.clientsubmit').addClass("hide");												//If code match allow submit
			$('input[name=instituition_code]').css('border','1px solid #FF8A00 !important');	//Diffrentiate border color if code is not correct
		}

		return false;
	});

	//Agreement Bio Form
	$(document).on('change','input[name=agreement]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.bio-form').removeClass('hide');
			return false;
		}

		if(status == false) 
		{
			$('.bio-form').addClass('hide');
			return false;
		}


	});

	//Deselect all variables once
	$(document).on('change','input[name=deselectall]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.dvariable').find('option').prop('selected',false);
			$('.dvariable').find('option[value=selectall]').removeAttr('selected');
			$('.dvariable').find('option[value=deselectall]').removeAttr('selected');
   			$('.chosentable').trigger('chosen:updated');

   			$('input[name=selectall]').attr('checked',false);
   			$('input[name=deselectall]').attr('checked',false);

   			$('#variableoverview').addClass('hide').children('span.content').html("");
		}
	});

	//Deselect all Category once
	$(document).on('change','input[name=deselectallcat]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.dcaggregation').find('option').prop('selected',false);
			$('.dcaggregation').find('option[value=selectall]').removeAttr('selected');
			$('.dcaggregation').find('option[value=deselectall]').removeAttr('selected');
   			$('.chosentable').trigger('chosen:updated');

   			$('select[name="caggregation[]').change();          //To trigger ajax request

   			$('input[name=selectallcat]').attr('checked',false);
   			$('input[name=deselectallcat]').attr('checked',false);

   			$('#cagoverview').addClass('hide').children('span.content').html("");
		}
	});

	//Deselect all Tales once From Admin
	$(document).on('change','input[name=deselectalltable]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.aggtables').find('option').prop('selected',false);
   			$('.chosentable').trigger('chosen:updated');

   			$('input[name=selectalltable]').attr('checked',false);
   			$('input[name=deselectalltable]').attr('checked',false);
		}
	});

	//Select all variable onces
	$(document).on('change','input[name=selectall]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.dvariable').find('option').prop('selected',true);
			$('.dvariable').find('option[value=selectall]').removeAttr('selected');
			$('.dvariable').find('option[value=deselectall]').removeAttr('selected');
   			$('.chosentable').trigger('chosen:updated');

   			$('input[name=deselectall]').attr('checked',false);

   			var data = $('.dvariable').val();

			data = data.filter(function(n){ return n != "" && n != " " && n != undefined});	//Filter of empty elements

			data = data.join();
   			
   			$('#variableoverview').removeClass('hide').children('span.content').html(data);
		}

	});

	//Select all tables at admin onces
	$(document).on('change','input[name=selectalltable]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.aggtables').find('option').prop('selected',true);
			$('.aggtables').find('option[value=selectall]').removeAttr('selected');
			$('.aggtables').find('option[value=deselectall]').removeAttr('selected');
   			$('.chosentable').trigger('chosen:updated');

   			$('input[name=deselectall]').attr('checked',false);
		}

	});

	$(document).on('change','input[name=selectallcat]',function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$('.dcaggregation').find('option').prop('selected',true);
			$('.dcaggregation').find('option[value=selectall]').removeAttr('selected');
			$('.dcaggregation').find('option[value=deselectall]').removeAttr('selected');
   			$('.chosentable').trigger('chosen:updated');

   			$('select[name="caggregation[]').change();

   			$('input[name=deselectallcat]').attr('checked',false);

   			var data = $('.dcaggregation').val();

			data = data.filter(function(n){ return n != "" && n != " " && n != undefined});	//Filter of empty elements

			data = data.join();
   			
   			$('#cagoverview').removeClass('hide').children('span.content').html(data);
		}

	});


	//Password Mixmatch
	$(document).on('keyup','#passwordcheck',function(e){

		var password2 = $(this).val();
			password1 = $('input[name=password]').val();

		if(password2 != password1) 
		{
			$('#passwordmatch').html('<i class="fonts ion-information-circled site-red"></i>password does not match');
		}else{
			$('#passwordmatch').html('');
		}

	});

	//Disallow less date//
	$(document).on('change blur','[name=to]',function(){
		var selected = $(this).val(),
			check    = $('[name=from]').val();

			if(Date.parse(check) > Date.parse(selected))
				{
					showarning('<b class="site-red">Warning: </b> Date selected in <b class="site-red">To</b> field must be greater than <b class="site-red">From</b> field');
					$('[name=to]').val('');
				}

	});

	/*Clients Overview
	**/
	$('[name=name]').bind('keyup',function()
	{
		$('#cl_name_overview').children('span.content').html( $(this).val() );
	});

	$('[name=name]').bind('blur',function()
	{
		$('#cl_name_overview').children('span.content').html( $(this).val() );
	});


	$('[name=address]',).bind('keyup',function()
	{
		$('#cl_add_overview').children('span.content').html( $(this).val() );
	});

	$('[name=address]').bind('blur',function()
	{
		$('#cl_add_overview').children('span.content').html( $(this).val() );
	});

	$('[name=email]').bind('keyup',function()
	{
		$('#cl_email_overview').children('span.content').html( $(this).val() );
	});

	$('[name=email]').bind('blur',function()
	{
		$('#cl_email_overview').children('span.content').html( $(this).val() );
	});

	$('[name=sex]').bind('keyup',function()
	{
		$('#cl_sex_overview').children('span.content').html( $(this).val() );
	});

	$('[name=sex]').bind('blur',function()
	{
		$('#cl_sex_overview').children('span.content').html( $(this).val() );
	});

	$('[name=phone]').bind('keyup',function()
	{
		$('#cl_phone_overview').children('span.content').html( $(this).val() );
	});

	$('[name=phone]').bind('blur',function()
	{
		$('#cl_phone_overview').children('span.content').html( $(this).val() );
	});

	$('[name=instituition]').bind('change',function()
	{
		$('#cl_institution_overview').children('span.content').html( $(this).val() );
	});

	$('[name=instituition]').bind('blur',function()
	{
		$('#cl_institution_overview').children('span.content').html( $(this).val() );
	});

	$('[name=designation]').bind('keyup',function()
	{
		$('#cl_designation_overview').children('span.content').html( $(this).val() );
	});

	$('[name=designation]').bind('blur',function()
	{
		$('#cl_designation_overview').children('span.content').html( $(this).val() );
	});

	// $('.ajaxEditForm').on('submit',function(e){
	$(document).on('submit','.ajaxEditForm',function(e){

		e.preventDefault();

		form = $(this);

		form.addClass('hide');

		//show notification
		$('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Updating Entries Please wait....');

		var form = $(this),
			url  = form.attr('action'),
			data = form.serialize();


			//Perform Ajax Post Request
			$.post(url,data)

			.done(function(response){

				//Save selected
				tableval       = $('select[name=table]').val(),
				aggregationval = $('select[name=laggregation]').val(),
				cggregationval = $('select[name="caggregation[]"]').val(),
				frequencyval   = $('select[name=frequency]').val(),
				variableval    = $('select[name="variable[]"]').val(),
				periodval      = $('select[name="period[]"]').val();
				toval      	   = $('input[name=to]').val();
				fromval        = $('input[name=from]').val();
				commentval     = $('textarea[name=comment]').val();

				datas = {};
				datas = {"tableval" : tableval,'aggregationval' : aggregationval,
						'cggregationval' : cggregationval,'frequencyval' : frequencyval,
						'variableval' : variableval,"periodval" : periodval,"toval" : toval,"fromval" : fromval,"commentval" : commentval};

				// console.log(datas);

				//When submission proposed we clone the form to use as an edit
				cloned = $(document).find('form.ajaxEditForm').clone();

				createEditForm(cloned,response,datas);			//Here we pass the cloned form and reponse id from server to be futher manipulated by a function

				var submissiontype = $('#submissiontype').val();
				
				$('.notifications').html('Request Data Successfully Updated');
				
				//Close Window
				setTimeout(function(){
					window.close();
				},3000);
				
				return false;
			})
			.fail(function(){
				console.log("Unable to submit request");
			});

			return false;
	});

});

/**
*Fill clients Form
**/
function fillclientform()
{
	$('#databaseoverview').addClass('hide');
	$('#clientsoverview').removeClass('hide');

	//Next
	$('#financeplace').addClass('hide');
	$('#clientdetails').removeClass('hide');
}

function submitrequest(id,action)
{
	var inputs = $.map($('form.ajaxForm').find('input[type=text]'),function(input,index){
		if(input.autocomplete != "off") return input.value;
	});

	var selects = $.map($('form.ajaxForm').find('select'),function(input,index){
		if(input.autocomplete != "off") return input;
	});

	var selectval = $.map(selects,function(input,index){

		input =  $(input).val() == "" || typeof $(input).val() == undefined ? "" : filteroffempty($(input).val());
		
		// console.log(input);

		if($.isArray(input))
		{
			if(input.length == 0) return "";
			if(input.length > 0)  return "Verified";
		}else{
			if(input == "" || input == " " || typeof input == undefined || input == null)  return "";
			if(input != "" || input != " " || typeof input != undefined || input != null)  return "Verified";
		}
	});

	//Disallow field submission is empty value is present
	if($.inArray("",inputs) > -1)
	{
		showarning('<b class="site-red">Warning: </b> No required field must be empty!');
		return false;
	}

	if($.inArray("",selectval) > -1)
	{
		showarning('<b class="site-red">Warning: </b> No required field must be empty!');
		return false;
	}
	//End check


	if(action == 'addnew')
	{
		$('#submissiontype').val('more');
		$('#' + id).closest('form').submit();
	}

	if(action == 'add')
	{
		$('#submissiontype').val('nomore');
		$('#' + id).closest('form').submit();
	}
}

/**	
* Proceed To Add Requests For Client 
*/
function submitAllRequest()
{
	fillclientform();

	//Append All Requests Id for clients
	requests.map(function(id){ $('form#addclient').append('<input type="hidden" name="requestid[]" value="' + id + '" />'); });
	$('#savereview').addClass('hide');  //Hide button to evade form clearing

	// $('form#addclient').append('<input type="hidden" name="requestid[]" value="' + response + '" />');
}

function submitClientForm()
{
	//All fields are required
	var inputs = $.map($('form#addclient').find('input[type=text]'),function(input,index){
		if(input.autocomplete != "off" && input.required == true) return input.value;
	});

	var selects = $.map($('form#addclient').find('select'),function(input,index){
		if(input.autocomplete != "off" && input.required == true) return input.value;
	});

	//Disallow field submission is empty value is present
	if($.inArray("",inputs) > -1)
	{
		$('#submitclientbutton').html('Yes');
		$(document).find('.modal').modal('hide');														//Close any previous opened modal
		showarning('<b class="site-red">Warning: </b> No required field in the bio form must be empty!');
		return false;
	}

	if($.inArray("",selects) > -1)
	{
		$('#submitclientbutton').html('Yes');
		$(document).find('.modal').modal('hide');														//Close any previous opened modal
		showarning('<b class="site-red">Warning: </b> No required field in the bio form must be empty!');
		return false;
	}
	//End check

	$('#submitclientbutton').html('<i class="fa fa-spinner fa-spin"></i> Submitting.....');
	// return false;

	$('#addclient').submit();
}

function loadpage(url,id,data)
{
	// console.log(url);
		//Fetch Tables
		$.get(url,data)

		.done(function(response){
			// console.log('response');
			$(id).html(response);
			//initialize chosen
			init();
			//remove spinner
			$('#select-spinner').remove();

		})

		.fail(function(){
		console.log("Cannot fetch data at this moment retry...!!");
		//Try to refetch
		loadpage(url,id,data);
		});
}

function init()
{
	$('.chosentable').chosen();
	// $('.datepicker').datepicker();
	initdatepicker();
	// $('.validaterqform').parsley();
}

function displayloader()
{
	$(document).find('#select-spinner').remove();

	//Display loader notification
	$('#finance').append('<div class="text-center" id="select-spinner"><i class="fa fa-spinner fa-spin"></i><br><span class="site-init">Fetching data...</span></div>');
}

function overview(data,view)
{
	var all   = ['#tableoverview','#lagoverview','#cagoverview','#freqoverview','#variableoverview','#periodoverview','#commentoverview'],
		field = ['.dtable','.dlaggregation','.dcaggregation','.dfrequency','.dvariable','.dperiod','.dcomment'];

		for(key in all)
		{
			if($(field[key]).val() != null && $(field[key]).val() != undefined && $(field[key]).val() != "" && view == field[key] )
			{

				if($.isArray(data)){

					data = data.filter(function(n){ return n != "" && n != " " && n != undefined && n != null});	//Filter of empty elements

					if(view == ".dperiod"){
							data = data.join('&nbsp; , &nbsp;');													//Multiple select returns array threfore join them
					}else if(view == ".dvariable")
					{
						setTimeout(function(){

							select = $('.dvariable').find('option:selected');

							select = $('.dvariable');

							data = $.map(select.find('option:selected'),function(input,key)
										{ 
											return input.value;
										});

						var indexa = data.indexOf('selectall');
						var indexb = data.indexOf('selectall');

						delete data[indexa];
						delete data[indexb];

						data = data.filter(function(n){ return n != "" && n != " " && n != undefined});				//Filter of empty elements

						data = data.join();

						$(all[key]).removeClass('hide').children('span.content').html(data);

						},3000);

					}
					else{ data = data.join(); }
				}

				// console.log($(all[key]).removeClass('hide').children('span.content'));

				$(all[key]).removeClass('hide').children('span.content').html(data);

				return false;
			}
			else{

					//Clear succesive fields if this is empty or null or undefined
					var start = field.indexOf(view),
						clear = all.slice(start);
						// present = start - 1;

					for(invalid in clear)
					{
						$(clear[invalid]).addClass('hide').children('span.content').html('');
					} 
			}
		}
		//undefined null ""
}

function savereview(bool)
{
	if(bool == true && requests.length > 1) $('#savereview').removeClass('hide');

	if(bool == false) $('#savereview').addClass('hide');
}


//Print A View
function printView(url)
{
	$('<iframe id="printFrame" name="printFrame" >').attr('src',url).appendTo('body');
	$("#printFrame").get(0).contentWindow.print();

	setTimeout(function(){
		$("#printFrame").remove();
	},5000);
	
	return false;
}

/**
* Copy a dom and paste into another
**/
function clonedom(copy,paste,required)
{
	var copy = $('#' + copy).clone();

	copy.find('label').html("");

	allids = $.map(copy.find('input'), function(input, key){
		//Clear input value
		input.value = "";
	  return input.id;
	});

	 //Remove required from input field for this
	 if(required == false)
	 {
	 	copy.find('input').map(function(){
		$(this).removeAttr("required");
		});
	 }

	 if(required == true)
	 {
	 	copy.find('input').map(function(){
		$(this).attr("required","required");
		});
	 }
	 

	copy.addClass('addedon');

	copy.addClass('margin-top-1');

	/*Action button*/
	copy.find('span.dombutton').attr('id',getRand());

	copy.find('span.dombutton').attr('onclick','removedom(this.id)');

	copy.find('span.dombutton').removeClass('ion-plus');

	copy.find('span.dombutton').addClass('ion-minus red');
	/*end*/

	$('#' + paste).append(copy);
}

/**
* Remove copy of dom element
**/
function removedom(id)
{
	$('#' + id).closest('div.addedon').remove();
}

/**
* getRandom string
**/
function getRand()
{
	d = new Date;
	return d.getTime() + "" + parseInt(Math.random() * 10000)
}

function hidemanualdate()
{
	$('#manualperiod').addClass('hide');
}

//Open child window to view edit
function editwindow(requestid)
{
	$('#firedit').attr('onclick',"actualedit(" + requestid + ")");
	$('#deletePrompt').modal();
}

function actualedit(requestid)
{
	$('#deletePrompt').modal('hide');
	window.open("editrequest?stash=editstash" + requestid,"Edit-Request","width=750,height=500,left=300,top=100,status=0,scrollbars=0;");
}


function deleteRequest(requestid)
{
	$('#requestoverviewof' + requestid).remove();

	//Reduce numbers of requets
	requests.splice(0,1);

	if(requests.length == 0)
	{
		window.location.reload();
	}
	
	//Remove From storage
	url = getUrl() + 'ddr/' + requestid;
	console.log(url);

	$.get(url)
	.done(function(){
		console.log('request deleted');
	})
	.fail(function(){
		console.log('request not deleted');
	});


}

//Get site absolute url
function getUrl()
{
	var host = window.location.host,
	path =  window.location.pathname.split("/")[1],
	url  = host + '/',
	protocol  = window.location.protocol;

	return 'http://' + url;
}

//Create an edit form in a 
//modal box for every single request
function createEditForm(clone,requestid,data)
{
		var newid    = 'editrequestform' + requestid,
		formurl      = 'editrequest';

	//change form id
	clone.attr('id',newid);

	//change form url
	clone.attr('action',formurl);

	//Change form button
	clone.find('#addnew').remove();
	clone.find('#addproceed').html('Update Request');

	//create div to stash 
	var stashdiv = $('<div id="editstash' + requestid + '" class="col-md-12 nopad"> </div>');

	stashname = "editstash" + requestid;

	var stash 		= stashdiv.html(clone),
		clonedstash = stash;

	// $(document).find('#financeplace').append(stash);

	involved = $.map(stash.find('select'), function(input, key){
							return input;
							});

	// console.log(involved);

	divs = ['#tables','#laggregation','#caggregation','#frequency','#variables' ];

	for(div in divs)
	{
		clonedstash.find(divs[div]).html('');
		// clonedstash.find(divs[div]).html( clonedstash.find('select').eq(div) );
		// console.log(div);
	}

	involved.map(function(value,key){
		clonedstash.find(divs[key]).html(value);
		// console.log(value);
	});

	clonedstash.find('select').each(function(){
		$(this).css('display','block');
	});


	//Update value to previously selecteds
	clonedstash.find('select[name=table]').val(data.tableval),
	clonedstash.find('select[name=laggregation]').val(data.aggregationval),
	clonedstash.find('select[name="caggregation[]"]').val(data.cggregationval),
	clonedstash.find('select[name=frequency]').val(data.frequencyval),
	clonedstash.find('select[name="variable[]"]').val(data.variableval),
	clonedstash.find('select[name="period[]"]').val(data.periodval);

	// console.log(stashname);

	clonedstash.find('.chosentable').trigger("chosen:updated");

	// init();

	// $(document).find('body').append(clonedstash);
	// $(document).find('#financeplace').append(clonedstash);

	setTimeout(function(){
		//Append hidden feilds and set value in form after 3sec when DOM is assumed to be ready

		identifier = $('<input type="hidden" value="" >');
		identifier.attr('name','editid');
		identifier.val(requestid);

		submitbtn = $('<button class="btn button-custom btn-custom-two pull-right" >Update Request</button>');

		clonedstash.find('form').removeClass('ajaxForm');
		clonedstash.find('#addproceed').remove();
		clonedstash.find('form').addClass('ajaxEditForm');
		clonedstash.find('form').append(identifier);

		clonedstash.find('#submitdiv').append(submitbtn);

		clonedstash.find('input[name=to]').val(data.toval);
		clonedstash.find('input[name=from]').val(data.fromval);
		clonedstash.find('textarea[name=comment]').val(data.commentval);

	},3000);

	setTimeout(function(){
		stashes[stashname] = {"file" : clonedstash,"isset" : false};
		console.log(stashes);
	},4000);
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function clickADatabase(id)
{
	// console.log(id);

	var lastClickedFromSurvey = id;

	if(lastClickedFromSurvey == '' || lastClickedFromSurvey.length == 0 || typeof lastClickedFromSurvey == undefined)
	{
		console.log('loading.. err..');
	}else{
		
		setTimeout(function(){
			$('#' + id).click();
		},2000);
		
	}
}

	function initdatepicker()
	{
		//destroy any previous initialization regardless
		$('.datepicker').datepicker("destroy");

		var freq = $('select[name=frequency]').find(':selected').data('type');

		if($.inArray(freq,['daily','weekly']) > -1)
		{
			$('[name=from]').attr('placeholder','dd/mm/YY');
			$('[name=to]').attr('placeholder','dd/mm/YY');

			$('.datepicker').datepicker({
							  showOptions: { direction: "up" }
							});
		}else{

				$('[name=from]').attr('placeholder','YY');
				$('[name=to]').attr('placeholder','YY');

				$('.datepicker').datepicker({
				changeMonth: false,
				changeYear: true,
				dateFormat: 'yy',
				yearRange: "1980:2020",
				showOptions: { direction: "up" },
				beforeShow : function (textbox, instance) {
				        instance.dpDiv.css({
				                    marginTop: '17%',
				                    borderRadius: '0px !Important'
				        });
				        // instance.find('.ui-datepicker-month').css('display','none !important');
		    		},
		    	onClose: function() {
		        		var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
		        		$(this).datepicker('setDate', new Date(iYear, 1));
		     	},

				}).focus(function() {
							var thisCalendar = $(this);
							$('.ui-datepicker-calendar').detach();
							$('.ui-datepicker-close').click(function() {
						var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
						var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
						thisCalendar.datepicker('setDate', new Date(year, month, 1));
						});
				});

		}

	}

	//Change the content of warning alert
	function showarning(phrase)
	{
		$('#warningPrompt').find('div.modal-body>h5').html(phrase);
		$('#warningPrompt').modal();
	}

	//function to remove empty from an array
	function filteroffempty(array)
	{
		if(!$.isArray(array)) return array;													//if argument is not array return back same value
		return array.filter(function(n){ return n != "" && n != " " && n != undefined});	//Filter of empty elements
	}

	//Confirm delete action
	function deletePromptAdmin(url)
	{
		$('#deletePromptAdmin').find('a').attr('href',url);
		$('#deletePromptAdmin').modal();
	}

	function deselectalloption(triggerer,selects)
	{
		//Deselect all variables once
		$(document).on('change',triggerer,function(e){
			var status = $(this).is(':checked');
			val    = $(this).val();

			if(status == true) 
			{
				$(selects).find('option').prop('selected',false);
	   			$('.chosentable').trigger('chosen:updated');

	   			$('input[name=selectall]').attr('checked',false);
	   			$(triggerer).attr('checked',false);
			}
		});
	}


	function selectalloption(triggerer,selects)
	{
		$(document).on('change',triggerer,function(e){
		var status = $(this).is(':checked');
		val    = $(this).val();

		if(status == true) 
		{
			$(selects).find('option').prop('selected',true);
   			$('.chosentable').trigger('chosen:updated');

   			$(triggerer).attr('checked',false);

   			var data = $('.dvariable').val();
		}

	});
	}