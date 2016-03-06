//Database Class
Anastat.Request = (function(){

    /*Private methods & properties here*/
    var Privatethat;

    var Privaterequests         = [];

    var Privatestashes          = [];

   
    /*
     Private method to initialize both
     chosen/select & datepicker together
     */
    var Privateinit = function()
    {
        Anastat.Intialization.select();

        Anastat.Intialization.datepicker();
    }

    var PrivateCleanUp = function()
    {
        //Clear select fields
        var DataField = ['#tables','#laggregation','#caggregation','#frequency','#variables','#period'],
            GeneralClass  = 'dart fa fa-caret-right databaselist';

        //Hide submit div
        $('#empty').addClass('hide');
        $('#submitdiv').addClass('hide');
        $('.dcomment').val('');

        //Clear and hide additional date div if any
        $('input[id=from]').val('');
        $('input[id=to]').val('');


        for (selector in DataField) {
            $(DataField[selector]).html('');
        };

        /* Clear database select
        *  Set same class for all
        */ 
        $('.databaselist').map(function(database){
            $(this).attr('class',GeneralClass).css('color','initial');
        });

        //Clear database field
        $('input[name=databasename]').val('');

        //Notification to continue
        $('#empty').removeClass('hide');
        $('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Proceed To Making More Requests');
    }

    /**
    * Hide request form & display client bio form
    */
    var PrivateFillClientForm  = function()
    {
        $('#databaseoverview').addClass('hide');
        // $('#clientsoverview').removeClass('hide');

        //Next
        $('#financeplace').addClass('hide');
        $('#clientdetails').removeClass('hide');
    }

    var PrivateSaveReview = function(bool)
    {
            if(bool == true && Privaterequests.length > 1) $('#savereview').removeClass('hide');

            if(bool == false) $('#savereview').addClass('hide');
    }

    var PrivateClearOverview = function()
    {
        var AllOverviewDivs = ['#databaseoverview',,'#variableoverview','#tableoverview','#lagoverview','#cagoverview',
                                                '#freqoverview','#periodoverview','#commentoverview'];

        //Clear overview div
        for (selector in AllOverviewDivs){
            $(AllOverviewDivs[selector]).addClass('hide').children('span.content').html('');
        };
    }

    //Ensure The To Field Is > From Field
    var PrivateCheckLessdate = function(e)
    {
        var e            = $(e.target); 
        var ToField      = e.val(),
            FromFeild    = $('[name=from]').val();

            if(Date.parse(FromFeild) > Date.parse(ToField))
                {
                    var warning = 'Date selected in <b>To</b> field must be greater than <b>From</b> field';
                    Anastat.Mixing.showarning(warning);
                    //Empty field
                    e.val('');
                }
    }

    var PrivateDidUserAgree = function(e)
    {
        e = $(e.target);

        var status = e.is(':checked');
            val    = e.val();

        if(status == true) 
        {
            $('.bio-form').removeClass('hide');
            $('#clientsoverview').removeClass('hide');

            return false;
        }

        if(status == false) 
        {
            $('.bio-form').addClass('hide');
            $('#clientsoverview').addClass('hide');
            return false;
        }
    }

    var PrivateSetClientType = function(e)
    {
        e = $(e.target);

        if(e.val() == "affiliate")
        {
            $('.institution_detail').removeClass("hide");
            $('.clientsubmit').addClass("hide");                                //Hide submit button until code match
        }else{
            $('.institution_detail').addClass("hide");
            $('.clientsubmit').removeClass("hide");                             //Remove hide from submit button since its not an affiliate client
        }
    }

    var PrivateBindOverview = function(element,view)
    {
        $(document).on('keyup change blur',element,function(e){ 
                    $(view).children('span.content').html( $(e.target).val() );
                });
    }

    /*Public methods & properties here*/
    return {

            /**
            * Submit a request || Submit & clear to allow 
            * another request 
            */
            SubmitRequest : function(id,action)
            {       
                    //Loop through all inputs and recover values
                    var inputs = $.map($('form.ajaxForm').find('input[type=text]'),function(input,index){
                        if(input.autocomplete != "off") return input.value;
                    });

                    //Loop through all selects
                    var selects = $.map($('form.ajaxForm').find('select'),function(input,index){
                        if(input.autocomplete != "off") return input;
                    });

                    var selectval = $.map(selects,function(input,index){

                        input =  $(input).val() == "" || typeof $(input).val() == undefined ? "" : Anastat.Mixing.FilterArray($(input).val());
                        
                        if($.isArray(input))
                        {
                            if(input.length == 0) return "";
                            if(input.length > 0)  return "Verified";        //Value is not empty
                        }else{
                            if(input == "" || input == " " || typeof input == undefined || input == null)  return "";
                            if(input != "" || input != " " || typeof input != undefined || input != null)  return "Verified";
                        }
                    });

                    //Disallow field submission if there is any empty value
                    if($.inArray("",inputs) > -1)
                    {
                        Anastat.Mixing.showarning('No required field must be empty!');
                        return false;
                    }

                    if($.inArray("",selectval) > -1)
                    {
                        Anastat.Mixing.showarning('No required field must be empty!');
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
            },

            TableValue : '',

            LevelValue : '',

            CategoryValue : '',

            FrequencyValue : '',

            VariableValue : '',

            PeriodValue : '',

            ToValue : '',

            FromValue : '',

            CommentValue : '',

            PostData : '',

            FormCopy : '',

            AllStashes : {},

            ValidError : '<b class="site-red">Warning:</b> Invalid institution code provided: kindly check if the code type is correct or institution registration is still valid otherwise select independent client',

        ProcessRequest : function(e)
        {
                    e.preventDefault();

                    e = $(e.target);

                    //show notification
                    $('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Submitting Entries Please wait....');

                    var form          = e,
                        url           = form.attr('action');
                        Anastat.Request.PostData = form.serialize();

                        //Perform Ajax Post Request
                        $.post(url,Anastat.Request.PostData)

                        .done(function(response){

                            Anastat.Request.TableValue     = $('select[name=table]').val();
                            Anastat.Request.LevelValue     = $('select[name=laggregation]').val();
                            Anastat.Request.CategoryValue  = $('select[name="caggregation[]"]').val();
                            Anastat.Request.FrequencyValue = $('select[name=frequency]').val();
                            Anastat.Request.VariableValue  = $('select[name="variable[]"]').val();
                            Anastat.Request.PeriodValue    = $('select[name="period[]"]').val();
                            Anastat.Request.ToValue        = $('input[name=to]').val();
                            Anastat.Request.FromValue      = $('input[name=from]').val();
                            Anastat.Request.CommentValue   = $('textarea[name=comment]').val();

                            //Save Clone Version For Later Use
                            Anastat.Request.FormCopy = $(document).find('form.ajaxForm').clone();

                            Anastat.Request.CreateEditForm(response);

                            var submissiontype = $('#submissiontype').val();
                            
                            //Line up requests
                            Privaterequests.push(response);

                        var RequestDetail = '<div class="col-md-12" id="requestoverviewof' + response + '" style="padding: 0px;"><h5 class="site-init" style="display: inline-block;font-size: 14px;padding-left: 2%;">';
                            RequestDetail += 'Request ';
                            RequestDetail += Privaterequests.length;
                            RequestDetail += '</h5><div class="col-md-12 nopad"><button onclick="Anastat.Mixing.printView(\'printrequest/'; 
                            RequestDetail += response;
                            RequestDetail += '/overview\')" class="btn btn-sm  button-custom btn-custom-two">Print</button>';
                            RequestDetail += '<a href="printrequest/'; 
                            RequestDetail += response;
                            RequestDetail += '/overview" target="_blank" class="btn btn-sm  button-custom btn-custom-two">View</a>';
                            RequestDetail += '<a onclick="Anastat.Request.NotifyEdit('; 
                            RequestDetail += response;
                            RequestDetail += ')"  class="btn btn-sm  button-custom btn-custom-two" title="Edit request"><span class="ion-edit"></span></a>';
                            RequestDetail += '<a onclick="Anastat.Request.DeleteRequest(';
                            RequestDetail += response; 
                            RequestDetail += ')"  class="btn btn-sm  button-custom btn-custom-two" title="Remove"><span class="ion-ios-trash"></span></a></div><hr></div>';

                            $('#requestlists').prepend(RequestDetail);

                            PrivateClearOverview();
                            
                            PrivateCleanUp();

                            //Once there are no more requests to add fill client form
                            if(submissiontype == "nomore")
                            {
                                Anastat.Request.SubmitAllRequest();
                            }
                            
                            //Show button to allow to submit all request without adding more
                            PrivateSaveReview(true);

                            return false;
                        })
                        .fail(function(){
                            Anastat.Errors['submit-request'] = "Unable to submit request";
                            console.log("Unable to submit request");
                        });

                        setTimeout(function(){
                            PrivateClearOverview();
                        },1200);

                        return false;
        },

        CreateEditForm : function(response)
        {
                    var  Newid         = 'editrequestform' + response,  //(response == requestid)
                         Formurl       = 'editrequest';
                         Stashdiv      = $('<div id="editstash' + response + '" class="col-md-12 nopad"> </div>'),
                         SelectionDivs = ['#tables','#laggregation','#caggregation','#frequency','#variables'];

                    //Change Form ID
                    Anastat.Request.FormCopy.attr('id',Newid);

                    //Change Form url
                    Anastat.Request.FormCopy.attr('action',Formurl);

                    //Change Form Button
                    Anastat.Request.FormCopy.find('#addnew').remove();
                    Anastat.Request.FormCopy.find('#addproceed').html('Update Request');

                    //Create Div To Stash 
                    var 

                    Stashname = "editstash" + response;

                    var Stash       = Stashdiv.html(Anastat.Request.FormCopy),
                        ClonedStash = Stash;

                    Involved = $.map(Stash.find('select'), function(input, key){
                                        return input;
                                    });

                    //Empty All Selection Div
                    for(div in SelectionDivs)
                    {
                        ClonedStash.find(SelectionDivs[div]).html('');
                    }

                    Involved.map(function(value,key){
                        ClonedStash.find(SelectionDivs[key]).html(value);
                    });

                    ClonedStash.find('select').each(function(){
                        $(this).css('display','block');
                    });


                    //Update value to previously selecteds
                    ClonedStash.find('select[name=table]').val(Anastat.Request.TableValue),
                    ClonedStash.find('select[name=laggregation]').val(Anastat.Request.LevelValue),
                    ClonedStash.find('select[name="caggregation[]"]').val(Anastat.Request.CategoryValue),
                    ClonedStash.find('select[name=frequency]').val(Anastat.Request.FrequencyValue),
                    ClonedStash.find('select[name="variable[]"]').val(Anastat.Request.VariableValue),

                    //Append SelectAll Button For Select Variables
                    VariableSelectAllButton  = '<div class="col-md-12 coverbd nopad"><div class="col-md-6"><div class="checkbox site-init font12"><label><input type="checkbox" name="all"> Select All Variables</label></div></div>';
                    VariableSelectAllButton += '<div class="col-md-6"><div class="checkbox site-init font12 text-right"><label><input type="checkbox" name="notall"> Deselect All Variables</label></div></div></div>';

                    ClonedStash.find('[name="variable[]"]').closest('div').after(VariableSelectAllButton);

                    //Append SelectAll Button For Select Category
                    CategorySelectAllButton  = '<div class="col-md-12 coverbd nopad"><div class="col-md-6"><div class="checkbox site-init font12"><label><input type="checkbox" name="all"> Select All Categories</label></div></div>';
                    CategorySelectAllButton += '<div class="col-md-6"><div class="checkbox site-init font12 text-right"><label><input type="checkbox" name="notall"> Deselect All Categories</label></div></div></div>';

                    ClonedStash.find('[name="caggregation[]"]').closest('div').after(CategorySelectAllButton);

                    setTimeout(function(){

                        //Append hidden feilds and set value in form after 3sec when DOM is assumed to be ready
                        var EditField = $('<input type="hidden" value="" >');
                            EditField.attr('name','editid');
                            EditField.val(response);

                        Submitbtn = $('<a onclick="javascript: window.close()" class="btn button-custom btn-custom-two pull-right font12" >Cancel</a><button class="btn button-custom btn-custom-two pull-right font12" >Update</button>');

                        ClonedStash.find('form').removeClass('ajaxForm');
                        ClonedStash.find('#addproceed').remove();
                        ClonedStash.find('form').addClass('ajaxEditForm');
                        ClonedStash.find('form').append(EditField);

                        ClonedStash.find('#submitdiv').removeClass('hide').append(Submitbtn);

                        //Fill In The Rest Of Details Into Form
                        ClonedStash.find('input[name=to]').val(Anastat.Request.ToValue);
                        ClonedStash.find('input[name=from]').val(Anastat.Request.FromValue);
                        ClonedStash.find('textarea[name=comment]').val(Anastat.Request.CommentValue);

                    },2000);

                    setTimeout(function(){
                        Anastat.Request.AllStashes[Stashname] = {"file" : ClonedStash,"isset" : false};
                    },4000);
        },

        DeleteRequest : function(requestid)
        {
            var notification = "Are sure you really want to delete this request!";

            Anastat.Mixing.showarningWithOption(notification,{"button" : "Yes I Know What Am Doing","action" : function()
                                                                                                                {
                                                                                                                    return Anastat.Request.PurgeRequest(requestid);
                                                                                                                } });
        },

        PurgeRequest : function(requestid)
        {
            $('#requestoverviewof' + requestid).remove();

            //Reduce numbers of requests
            Privaterequests.splice(0,1);

            if(Privaterequests.length == 0) window.location.reload();

            //Remove From storage
            url = Anastat.Mixing.getUrl() + 'ddr/' + requestid;

            $.get(url)
                .done(function(){
                    Anastat.Errors['DeleteRequest'] = 'request deleted';
                })
                .fail(function(Exception){
                    Anastat.Errors['DeleteRequest'] = 'Request Not deleted : ' + Exception;
                });
        },

        //Confirm If User Actually What To Edit
        NotifyEdit : function(requestid)
        {
            var notification = "Are you sure you want to edit this request ?";

            Anastat.Mixing.showarningWithOption(notification,{"button" : "Yes",
                                                              "title" : "<h4 class='site-red ion-android-notifications-none'>&nbsp;Notification</h4>",
                                                              "action" : function()
                                                                        {
                                                                            return Anastat.Request.EditRequest(requestid);
                                                                        } });
        },

        //Edit Request
        EditRequest : function(requestid)
        {
            alertify.alert().close();
            window.open("editrequest?stash=editstash" + requestid,"Edit-Request","width=1000,height=500,left=200,top=100,status=0,scrollbars=0;");
        },

        //Submit All Queued Requests
        SubmitAllRequest : function()
        {
            PrivateFillClientForm();

            //Append All Requests Id for clients
            Privaterequests.map(function(id){ $('form#addclient').append('<input type="hidden" name="requestid[]" value="' + id + '" />'); });
            //Hide button to evade form clearing
            $('#savereview').addClass('hide');  
        },

        UpdateRequest : function(e)
        {
            e.preventDefault();

            e = $(e.target);

            var Form = e;

            Form.addClass('hide');

            //show notification
            $('.notifications').html('<i class="fa fa-info-circle site-red info"></i> Updating Entries Please wait....');

            var url  = Form.attr('action'),
                data = Form.serialize();


                //Perform Ajax Post Request
                $.post(url,data)

                .done(function(response){

                Anastat.Request.TableValue     = $('select[name=table]').val();
                Anastat.Request.LevelValue     = $('select[name=laggregation]').val();
                Anastat.Request.CategoryValue  = $('select[name="caggregation[]"]').val();
                Anastat.Request.FrequencyValue = $('select[name=frequency]').val();
                Anastat.Request.VariableValue  = $('select[name="variable[]"]').val();
                Anastat.Request.PeriodValue    = $('select[name="period[]"]').val();
                Anastat.Request.ToValue        = $('input[name=to]').val();
                Anastat.Request.FromValue      = $('input[name=from]').val();
                Anastat.Request.CommentValue   = $('textarea[name=comment]').val();

                //Save Clone Version For Later Use
                Anastat.Request.FormCopy = $(document).find('form.ajaxEditForm').clone();

                Anastat.Request.CreateEditForm(response);

                $('.notifications').html('Request Data Successfully Updated');
                            
                //Close Window
                setTimeout(function(){
                    window.close();
                },3000);
                            
                    return false;
                })

                .fail(function(Exception){
                    Anastat.Errors['UpdateRequest'] = 'Error updating request' + Exception;
                });

            return false;
        },

        ValidateAffiliateCode : function(e)
        {
            e = $(e.target);

            code = $('[name=instituition]').find(':selected').data('code').toLowerCase();
            
            var invalid_info = Anastat.Request.ValidError;

            if(code != e.val().toLowerCase() && e.val().length == code.length || e.val().length > code.length) Anastat.Mixing.showarning(invalid_info);

            if(code == e.val().toLowerCase())
            {
                var info = '<i class="fonts ion-ios-checkmark green"></i> Valid code supplied';

                $('.codeinfo').html(info);
                $('.clientsubmit').removeClass("hide");     //If code match allow submit
            } 

            if(code != e.val().toLowerCase())
            {
                var info = '<i class="fonts ion-information-circled site-red"></i>  Provide correct institution code to activate submit button';

                $('.codeinfo').html(info).removeClass('hide');
                $('.clientsubmit').addClass("hide");                                                //If code match allow submit
                $('input[name=instituition_code]').css('border','1px solid #FF8A00 !important');    //Diffrentiate border color if code is not correct
            }

            return false;
        },

        //Display client overview content
        ClientOverview : function(e)
        {
            var Divs = [ '[name=name]','[name=address]','[name=email]','[name=sex]','[name=phone]','[name=instituition]','[name=designation]' ];
            
            var OverviewDivs = ['#cl_name_overview','#cl_add_overview','#cl_email_overview','#cl_sex_overview','#cl_phone_overview','#cl_institution_overview','#cl_designation_overview']
            
            PrivateBindOverview(Divs[0],OverviewDivs[0]);
            PrivateBindOverview(Divs[1],OverviewDivs[1]);
            PrivateBindOverview(Divs[2],OverviewDivs[2]);
            PrivateBindOverview(Divs[3],OverviewDivs[3]);
            PrivateBindOverview(Divs[4],OverviewDivs[4]);
            PrivateBindOverview(Divs[5],OverviewDivs[5]);
            PrivateBindOverview(Divs[6],OverviewDivs[6]);
        },

        SubmitClientDetails : function(e)
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
                $(document).find('.modal').modal('hide');                                                       //Close any previous opened modal

                Anastat.Mixing.showarning('No required field in the bio form must be empty!');

                return false;
            }

            if($.inArray("",selects) > -1)
            {
                $('#submitclientbutton').html('Yes');
                $(document).find('.modal').modal('hide');                                                       //Close any previous opened modal

                Anastat.Mixing.showarning('<b class="site-red">Warning: </b> No required field in the bio form must be empty!');

                return false;
            }
            //End check

            //Check for valid email
            var email = $('#clientemail').val();

            if(!Anastat.Mixing.ValidateEmail(email)) 
            {
                Anastat.Mixing.showarning('Invalid Email Supplied!');

                return false;
            }
            //End check

            //Check for valid code for affiliated clients
            if($('select[name=clienttype]').val() == "affiliate")
            {
                var instituition = $('select[name=instituition]').find('option:selected').data('code').toLowerCase();
                var status       = $('select[name=instituition]').find('option:selected').data('status');
                var code         = $('input[name=instituition_code]').val().toLowerCase();

                if(status == 0)
                {
                    Anastat.Mixing.showarning(Anastat.Request.ValidError);

                    return false;
                }

                if(instituition != code)
                {
                    Anastat.Mixing.showarning(Anastat.Request.ValidError);

                    return false;
                }

                if(instituition == 'cb5e1' || code == 'cb5e1')
                {
                    Anastat.Mixing.showarning(Anastat.Request.ValidError);

                    return false;
                }
            }
            //End check

            $('#submitclientbutton').html('<i class="ion-load-c fa-spin"></i> Submitting.....');
            // return false;

            $('#addclient').submit();
        },

        /*Listen for events on request page & carry out respective method*/
        attachEvents : function()
        {
            Privatethat = this;

            //Listen ToProcess Request Form
            $('.ajaxForm').on('submit',function(e){ Privatethat.ProcessRequest(e) });

            //Listen To Process Client Form
            $(document).on('submit','.ajaxEditForm',function(e){ Privatethat.UpdateRequest(e) });
            //Validate That User To Date Value Should Be Greater Than From 
            $(document).on('change blur','[name=to]',function(e){ PrivateCheckLessdate(e) });
            //Allow User To Fill Form If User Agree To Terms
            $(document).on('change','input[name=agreement]',function(e){ PrivateDidUserAgree(e) });

            $(document).on('click','select[name=clienttype]',function(e){ PrivateSetClientType(e) });

            $(document).on('keyup blur','input[name=instituition_code]',function(e){ Anastat.Request.ValidateAffiliateCode(e) });
            $(document).on('change','select[name=instituition]',function(e){ Anastat.Request.ValidateAffiliateCode(e) });


            //Call Method To Listen & Update Client Overview
            Privatethat.ClientOverview();

         }
    }

})();
