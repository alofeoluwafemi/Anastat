//Database Class
Anastat.Database = (function(){

    /*Private methods & properties here*/
    var Privatethat;

    var PrivatedatabaseSelected = false;

    var Privateperiodisset      = false;

    var Privatefieldisset       = false;

    var Privaterequests         = [];

    var Privatesavereview = function(bool)
    {
            if(bool == true && Privaterequests.length > 1) $('#savereview').removeClass('hide');

            if(bool == false) $('#savereview').addClass('hide');
    };

    /*
     Private method to initialize both
     chosen & datepicker together
     */
    var Privateinit = function()
    {
        Anastat.Intialization.select();

        Anastat.Intialization.datepicker();
    };

    /*Remove loader notification*/
    var Privateremovespinner = function()
    {
        $('#select-spinner').remove();
    };

    /*Display loader notification*/
    var Privatedisplayloader = function()
    {
        Privateremovespinner();
        
        $('#finance').append('<div class="text-center" id="select-spinner">' +
                            '<i class="ion-load-c fa fa-spin"></i><br><span class="site-init">Fetching data...</span>' +
                            '</div>');
    };

    /*
     Fetch a particular page
     via url pf the page
     */
    var Privateloadpage = function(url,id,data,timeset)
    {
        if(!arguments[3]) var  timeset = 3000;          //Set Default time to setOut

        setTimeout(function()
        {
            //Fetch Page
            $.get(url,data)

                .done(function(response){

                    $(id).html(response);

                    $(id).focus();

                    //Initialize Chosen & Datepicker
                    Privateinit();

                    //remove spinner
                    Privateremovespinner();

                })

                .fail(function(Exception){
                    Anastat.Errors['loadpage'] = Exception;
                    console.log("Cannot fetch data at this moment try back!!");

                    //Try back
                    Privateloadpage(url,id,data);
                });

        },timeset);
    };

    /*Clear successive divs under a select*/
    var PrivateClearSuccessiveDiv = function(divs)
    {
        for(selector in divs)
        {
            $(divs[selector]).html('');
        }
    }

    /*Change & determine visibility of submit button*/
    var PrivateDisplaySubmitButton = function(bool)
    {
        if(bool == true)  $('#submitdiv').removeClass('hide');
        if(bool == false) $('#submitdiv').addClass('hide');
    }

    /*Public methods & properties here*/
    return {

        //Load Database Tables
        /**
         * @return {boolean}
         */
        LoadTable : function(e)
        {
            e = $(e.target)

            path = window.location.pathname.replace("/","");
            lastid = e.attr('id');

            /*
             If database was selected from survey page
             redirect back to home then
             click last database triggered by user
             from previous request
             */
            if(path == "surveyresearch")
            {
                Anastat.Mixing.setCookie('databaseClickedFromSurvey',lastid, 1);
                //Return to index for selection
                window.location.assign('/');
            }
            else{
                Anastat.Mixing.setCookie('databaseClickedFromSurvey','',-1);
            }

            var generalclass  = 'ion-android-arrow-dropright dart databaselist',
                selectedclass = 'ion-android-arrow-dropright dart databaselist',
                type          = e.data('type'),
                url           = e.data('fetch'),
                id            = "#tables",
                data          =  {},
                divs 		  =  ['#tables','#laggregation','#caggregation','#frequency','#variables','#period'],
                databasename  = e.data('code');

            //Set same class for all
            $('.databaselist').map(function(database){
                $(this).attr('class',generalclass).css('color','#444');
            });

            //Clear successive divs
            PrivateClearSuccessiveDiv(divs);

            //Excempt clicked one
            e.attr('class',selectedclass).css('color','rgba(107, 21, 161, 0.57)');

            //Hide empty notification
            $('#empty').addClass('hide');

            //Display loader notification
            Privatedisplayloader();

            //Fetch result page
            Privateloadpage(url,id);

            //Add to overview
            $('#databaseoverview').removeClass('hide')
                .children('span.content')
                .html(databasename);

            $('input[name=databasename]').val(databasename);

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(false);

            //Display reviewsave
            Privatesavereview(false);
        },

        /*Load Level Of Aggregations Based On Database Selection*/
        LoadLevel : function(e)
        {
            e = $(e.target);

            var url       = 'laggregation/' + e.find(':selected').data('id'),
                id        = "#laggregation",
                data      =  {},
                divs 	  =  ['#laggregation','#caggregation','#frequency','#variables','#period'],
                datainput =  $('select[name=table]').val();

            //Clear successive divs
            PrivateClearSuccessiveDiv(divs);

            //Display loader notification
            Privatedisplayloader();

            //Fetch result pgae
            Privateloadpage(url,id);

            //Overview
            Anastat.Mixing.Overview(datainput,'.dtable');

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(false);

            //Display reviewsave
            Privatesavereview(false);

            return false;
        },

        LoadCategory : function(e)
        {
            e = $(e.target);

            var url           = 'caggregation/' + e.find(':selected').data('id'),
                id            = "#caggregation",
                data          =  {},
                divs 		  =  ['#caggregation','#frequency','#variables','#period'],
                datainput 	  =  $('select[name=laggregation]').val();

            //Clear successive divs
            PrivateClearSuccessiveDiv(divs);

            //Display loader notification
            Privatedisplayloader();

            //Fetch result pgae
            Privateloadpage(url,id);

            //Overview
            Anastat.Mixing.Overview(datainput,'.dlaggregation');

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(false);

            //Display reviewsave
            Privatesavereview(false);

            return false;
        },

        /**
         * Fetch Frequencies
         * @return {boolean}
         */
        LoadFrequency : function(e)
        {
            var url       = 'frequency/' + $('select[name=table]').find(':selected').data('id'),
                id            = "#frequency",
                data          =  {},
                divs 		  =  ['#period'],
                datainput 	  =  $('select[name="caggregation[]"]').val();

            //Display loader notification
            Privatedisplayloader();

            //Fetch result pgae
            Privateloadpage(url,id);

            //Overview
            Anastat.Mixing.Overview(datainput,'.dcaggregation');

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(false);

            //Display reviewsave
            Privatesavereview(false);

            return false;
        },

        LoadVariable : function(e)
        {
            var table             = $('select[name=table]').find(':selected').data('id'),
                level             = $('select[name=laggregation]').find(':selected').data('id'),
                freq              = $('select[name=frequency]').find(':selected').data('id'),
                cal_type          = $('select[name=frequency]').find(':selected').data('caltype'),
                var_url           = 'variable/' + table + '/' + level + '/' + freq,
                var_id            = "#variables",
                period_url        = 'periods/' + $('select[name=frequency]').find(':selected').data('id')
                period_id         = "#period",
                data              =  {},
                divs 		      =  ['.null'],
                datainput         =  $('select[name=frequency]').val();

            //Clear successive divs
            PrivateClearSuccessiveDiv(divs);

            //Display loader notification
            Privatedisplayloader();

            //Fetch variable result page
            Privateloadpage(var_url,var_id,{},1500);

            //Fetch period result page
            var data                  = {};
                data['table']         = table,
                data['level']         = level,
                data['freq']          = freq;
                data['calendar_type'] = cal_type;

            Privateloadpage(period_url,period_id,data,1500);

            //Overview Frequency
            Anastat.Mixing.Overview(datainput,'.dfrequency');
            

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(false);

            //Display reviewsave
            Privatesavereview(false);

            return false;
        },

        /**
         * Display variable overview
         */
        TouchVariable : function(e)
        {
            e = $(e.target);

            var datainput = $('select[name="variable[]"]').val();

            //Overview Variable
            Anastat.Mixing.Overview(datainput,'.dvariable');
        },

        TouchPeriod : function(e)
        {
            return this.TouchToField(e);
        },

        TouchComment : function(e)
        {
            e = $(e.target);

            var datainput 	  =  $('textarea[name=comment]').val();

            //Overview
            Anastat.Mixing.Overview(datainput,'.dcomment');

            //Display reviewsave
            Privatesavereview(false);

            return false;
        },

        TouchToField : function(e)
        {
            e = $(e.target);

            if(e.val() == null)
            {
                //Determine visibility of submit button
                PrivateDisplaySubmitButton(false);

                return false;
            }

            //Determine visibility of submit button
            PrivateDisplaySubmitButton(true);

            var datainput 	  =  [$('[name=from]').val(),$('[name=to]').val()];

            data = datainput.join('&nbsp; - &nbsp;');   //Multiple select returns array & concat them

            setTimeout(function(){ $('#periodoverview').removeClass('hide').children('span.content').html(data) },1200);

            //Overview
            Anastat.Mixing.Overview(datainput,'.dperiod');

            //Display reviewsave
            Privatesavereview(true);

            return false;
        },

        /**
         * Select all option in a particular field
         * @param e
         * @constructor
         */
        SelectAll : function(e)
        {
            e = $(e.target);

            var selectField = e.closest('div.coverbd')
                                .prev('div')
                                .find('select');

            $.map(selectField.find('option'),function(input,index){
                return input.selected = true;
            });

            //For chosen plugin
            $('.chosentable').trigger('chosen:updated');

            //For select2 plugin
            Anastat.Intialization.select();

            selectField.find('option[value=all]').removeAttr('selected');
            selectField.find('option[value=notall]').removeAttr('selected');

            selectField.change();

            e.attr('checked',false);
        },

        /**
         * Deselect all option in a particular field
         * @param e
         * @constructor
         */
        DeSelectAll : function(e)
        {
            e = $(e.target);

            var selectField = e.closest('div.coverbd')
                .prev('div')
                .find('select');

            $.map(selectField.find('option'),function(input,index){
                return input.selected = false;
            });

            //For chosen plugin
            $('.chosentable').trigger('chosen:updated');

            //For select2 plugin
            Anastat.Intialization.select();

            selectField.find('option[value=all]').removeAttr('selected');
            selectField.find('option[value=notall]').removeAttr('selected');

            selectField.change();

            e.attr('checked',false);
        },

        /*Listen for events on request page & carry out respective method*/
        attachEvents : function()
        {
            Privatethat = this;

            $(document).on('click','.databaselist', function(e){ Privatethat.LoadTable(e) });                       //Tables

            $(document).on('change','select[name=table]',function(e){ Privatethat.LoadLevel(e) });                  //Levels

            $(document).on('change','select[name=laggregation]',function(e){ Privatethat.LoadCategory(e) });        //Category

            $(document).on('change','select[name="caggregation[]"]',function(e){ Privatethat.LoadFrequency(e) });   //Frequency

            $(document).on('change','select[name=frequency]',function(e){ Privatethat.LoadVariable(e) });           //Variable

            // Display Comment Overview
            $(document).bind('keyup','textarea[name=comment]',function(e){ Privatethat.TouchComment(e) });
            $(document).bind('blur','textarea[name=comment]',function(e){ Privatethat.TouchComment(e) });
            // End

            $(document).bind('change','select[name="variable[]"]',function(e){ Privatethat.TouchVariable(e) });

            $(document).on('change','.dperiod',function(e){ Privatethat.TouchToField(e) });
        }
    }

})();
