$(function(){

    /*Initialize DatePicker*/
    Anastat.Intialization.initdatepicker();

    Anastat.Intialization.select();

    Anastat.Intialization.tinymce();

    /*Enable number lock on fields with this class*/
    Anastat.Mixing.numlock();

    /*Fade Notification*/
    Anastat.Mixing.fadeOutNotification();

    /*Attach Request Listeners To DOM to take appropriate actions for data selection*/
    Anastat.Database.attachEvents();

    /*Trigger Click On Database Select Made From Another Page Apart From Index */
    LastDB = Anastat.Mixing.getCookie('databaseClickedFromSurvey');

    /*Click Open A DB if A Previous Selection Was Made*/
    Anastat.Mixing.SelectADatabase(LastDB);

    /*Attach Request Listeners To DOM to take appropriate actions for data selection*/
    Anastat.Intialization.attachEvents();

    Anastat.Request.attachEvents();

});