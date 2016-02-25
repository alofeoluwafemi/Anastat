//Global functions are defined in this file

//Class Mixing
Anastat.Mixing = (function(){

    /*Private methods & properties here*/
    var PrivateLockField;

    var Privatecopy;

    var Privatepaste;

    var PrivatePasswordOne;

    var PrivatePasswordTwo;


    /*Public methods & properties here*/
    return {

        /*
         Allow only numbers for this field
         @param field => class of input to validate
         */
        numlock : function()
        {
            if(arguments.length > 0) this.PrivateLockField = field;

            if(arguments.length == 0) this.PrivateLockField = 'numlock';

            $('.numlock').bind('keyup',function(e){
                var present = $(this).val();
                if(!$.isNumeric(present) && present.length > 0)
                {
                    alert('Only number allowed!');
                    return false;
                }
            });
        },

        /*
         Convert a page into printable
         by passing the url into the method
         */
        printView : function(url)
        {
            $('<iframe id="printFrame" name="printFrame" >').attr('src',url).appendTo('body');
                $("#printFrame").get(0).contentWindow.print();

            setTimeout(function(){
                $("#printFrame").remove();
            },5000);

            return false;
        },

        /*Get site absolute url*/
        getUrl : function()
        {
                var host = window.location.host,
                    path =  window.location.pathname.split("/")[1],
                    url  = host + '/',
                    protocol  = window.location.protocol;

                return 'http://' + url;
        },

        /**
         * Check if password confirmation field matches
         */
        PasswordCheck : function(e)
        {
            e = $(e.target);

            PrivatePasswordTwo = e.val();
            PrivatePasswordOne = $('input[name=password]').val();

            var ErrorMessage = '<i class="fonts ion-information-circled site-red"></i>password does not match';

            if(PrivatePasswordTwo != PrivatePasswordOne)
            {
                $('#passwordmatch').html(ErrorMessage);
            }else{
                $('#passwordmatch').html('');
            }
        },

        /*
         Clone a window dom element
         and paste into another element
         */
        CloneDOM : function(copy,paste)
        {
            /**
             * Copy a DOM element and paste into another
             */
                Privatecopy = copy;

                Privatepaste = paste;

                Privatecopy = $('#' + Privatecopy).clone();

                Privatecopy.find('label').html("");

                allids = $.map(Privatecopy.find('input'), function(input, key){
                    //Clear input value
                    input.value = "";
                    return input.id;
                });

                //Remove required from input field for this
                Privatecopy.find('input').map(function(){
                    $(this).removeAttr("required");
                });

                Privatecopy.addClass('addedon');

                Privatecopy.addClass('margin-top-1');

                //Action button
                Privatecopy.find('span.dombutton').attr('id',this.getRand());

                Privatecopy.find('span.dombutton').attr('onclick','Anastat.Mixing.RemoveDOM(this.id)');

                Privatecopy.find('span.dombutton').removeClass('ion-plus');

                Privatecopy.find('span.dombutton').addClass('ion-minus red');

                $('#' + Privatepaste).append(Privatecopy);
        },

        /*Remove an injected DOM element*/
        RemoveDOM : function(id)
        {
            $('#' + id).closest('div.addedon').remove();
        },

        AdminPromptDelete : function(url)
        {
            $('#deletePromptAdmin').find('a').attr('href',url);
            $('#deletePromptAdmin').modal();
        },

        /*Generate a random string*/
        getRand : function()
        {
                d = new Date;
                return d.getTime() + "" + parseInt(Math.random() * 10000)
        },

        //Fade out notification by 9secs
        fadeOutNotification : function()
        {
            $('.notify').fadeOut(18000);
        },

        //Fade out notification by 18secs
        fadeOutNotificationMore : function()
        {
            $('.notifyLater').fadeOut(18000);
        },

        /*Remove empty & undefined from an array*/
        filteroffempty : function(array)
        {
            if(!$.isArray(array)) return array;												      //if argument is not array
                                                                                                  // return back same value

            return array.filter(function(n){ return n != "" && n != " " && n != undefined});      //Filter of empty elements
        },

        /*Display warning alert prompt*/
        showarning : function(phrase)
        {
                //Hide any preopened modal box
                $('.modal').modal('hide');
                $('#warningPrompt').find('div.modal-body>h5').html(phrase);
                $('#warningPrompt').modal();
        },

        /*Click a database selected from referer page*/
        clickADatabase : function(id)
        {
                var lastClickedFromSurvey = id;

                if(lastClickedFromSurvey == '' || lastClickedFromSurvey.length == 0 || typeof lastClickedFromSurvey == undefined)
                {
                    Anastat.Errors['clickADatabase'] = 'No Database found to activate!';
                }else{
                    setTimeout(function(){
                        $('#' + id).click();
                    },2000);
                }
        },

        setCookie : function(cname, cvalue, exdays)
        {
            var d = new Date();

            d.setTime(d.getTime() + (exdays*24*60*60*1000));

            var expires = "expires="+d.toUTCString();
            document.cookie = cname + "=" + cvalue + "; " + expires;
        },

        getCookie : function(cname)
        {
            var name = cname + "=";

            var ca = document.cookie.split(';');

            for(var i=0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
            }

            return "";
        },

        SelectADatabase : function(id)
        {
            var lastClickedFromSurvey = id;

            if(lastClickedFromSurvey == '' || lastClickedFromSurvey.length == 0 || typeof lastClickedFromSurvey == undefined)
            {
                Anastat.Errors['selecTaDatabase'] = 'No previous database to select';
            }else{
                setTimeout(function(){
                    $('#' + id).click();
                },2000);
            }
        },

        FilterArray : function(array)
        {
            if($.isArray(array)) return array.filter(function(n){ return n != "" && n != " " && n != undefined && n != null});

            return array;
        },

        ValidateEmail : function(email)
        {
            var format = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

            if(email.match(format)) return true;

            return false;
        },

        /**
         * @return {boolean}
         * @return {boolean}
         */
        Overview : function(data,view)
        {
                //Overview ids
                var DivIds   = ['#tableoverview','#lagoverview','#cagoverview','#freqoverview',
                                  '#variableoverview','#periodoverview','#commentoverview'],

                    //Field className
                    fieldClasses = ['.dtable','.dlaggregation','.dcaggregation','.dfrequency',
                            '.dvariable','.dperiod','.dcomment'];

                for(key in DivIds)
                {
                    if($(fieldClasses[key]).val() != null && $(fieldClasses[key]).val() != undefined
                        &&
                        $(fieldClasses[key]).val() != "" && view == fieldClasses[key] )
                    {

                        if($.isArray(data)){

                            data = Anastat.Mixing.FilterArray(data);

                            if(view == ".dperiod")
                            {

                                data = data.join('&nbsp; , &nbsp;');	//Multiple select returns array & concat them

                                $(DivIds[key]).removeClass('hide').children('span.content').html(data);

                                break;
                            }else if(view == ".dvariable")
                            {
                                // setTimeout(function(){

                                    select = $('.dvariable');

                                    data = $.map(select.find('option:selected'),function(input,key)
                                    {
                                        return input.value;
                                    });

                                    var indexa = data.indexOf('selectall');
                                    var indexb = data.indexOf('selectall');

                                    delete data[indexa];
                                    delete data[indexb];

                                    data = Anastat.Mixing.FilterArray(data);

                                    data = data.join('<br/>');

                                    $(DivIds[key]).removeClass('hide').children('span.content').html(data);

                                // },3000);

                            }
                            else{ data = data.join(); }
                        }

                        
                        $(DivIds[key]).removeClass('hide').children('span.content').html(data);

                        return false;
                    }
                    else{

                        //Clear successive fields if this is empty or null or undefined
                        var start = fieldClasses.indexOf(view),
                            clear = DivIds.slice(start);

                        for(invalid in clear)
                        {
                            $(clear[invalid]).addClass('hide').children('span.content').html('');
                        }
                    }
                }
        }

    }
})();
