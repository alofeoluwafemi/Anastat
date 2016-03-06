/*Turn On The Light Here*/

/*
 Include hierarchy for files
 @bootstarp.js
 @mixings.js
 @database.js
*/

/*
 Define Application Base Namespace
 Won't Want To Define Into Global(window) Namespace
 */
Anastat = function() {};

//Define a global error property
Anastat.Errors = {};

/*
 Class to take care of external plugins
 needed to be initialized
 */
Anastat.Intialization = (function()
{
    /*Private methods & properties stays here*/
    var private;

    /*Public methods & properties are returned*/
    return {

        //Initialize chosen plugin
        select : function()
        {
            try{

                $('.chosentable').chosen();

                $('.chosentablesingle').select2(
                    {
                        closeOnSelect: true,
                        width: "resolve",

                    });

                $('.chosentablemultiple').select2(
                    {
                        closeOnSelect: false,
                        multiple: true,
                        width: "resolve",

                    });
            }catch(Exception)
            {
                console.log(Exception);
                Anastat.Errors['chosen'] = Exception;
            }
        },

        //Initialize tinymce
        tinymce : function()
        {
            try{
                tinymce.init({ selector:'.database_help' });
            }catch(Exception)
            {
                console.log(Exception);
                Anastat.Errors['chosen'] = Exception;
            }
        },

        datepicker : function()
        {
            $('.datepicker').datepicker();
        },

        initdatepicker : function()
        {
                //Destroy Any Previous Initialization
                $('.datepicker').datepicker("destroy");

                $('[name=from]').attr('placeholder','dd/mm/YY');
                $('[name=to]').attr('placeholder','dd/mm/YY');

                $('.datepicker').datepicker({
                     showOptions: { direction: "up" }
                });
        },

        /*Trait to allow use of some methods*/
        augment : function(receivingClass,givingClass)
        {
                if(arguments[2]) {                                                                     // Only give certain methods.
                    for(var i = 2, len = arguments.length; i < len; i++) {
                        receivingClass.prototype[arguments[i]] = givingClass.prototype[arguments[i]];
                    }
                }
                else {                                                                                 // Give all methods.
                    for(methodName in givingClass.prototype) {
                        if(!receivingClass.prototype[methodName]) {
                            receivingClass.prototype[methodName] = givingClass.prototype[methodName];
                        }
                    }
                }
        },

        attachEvents : function()
        {
            //Select all options in field
            $(document).on('change','input[name=all]',function(e){ Anastat.Database.SelectAll(e) });

            //Deselect all options in field
            $(document).on('change','input[name=notall]',function(e){ Anastat.Database.DeSelectAll(e) });

            //Admin Area
            $(document).on('keyup','#passwordcheck',function(e){ Anastat.Mixing.PasswordCheck(e) });

            $(document).on('blur','#clientemail',function(e){ 

                var email = $(this).val();

                if(!Anastat.Mixing.ValidateEmail(email)) Anastat.Mixing.showarning('Invalid Email Supplied!');

            });

            //Enable scrolls on the following elements IDS
            var scrolls = ['microdatabases','macrodatabases','overviewarea','requestarea'];

            for(element in scrolls)
            {
                var databaselist = document.getElementById(scrolls[element]);

                Ps.initialize(databaselist);
            }

            //Pace
            paceOptions = {
                elements: true,
            };

            $(document).ajaxStart(function() { Pace.restart(); });
        }
    }
})();