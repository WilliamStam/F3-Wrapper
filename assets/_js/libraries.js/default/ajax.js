$(document).ajaxComplete(function(event, request, settings) {
    
    if (request){
        if (request.responseJSON){
            if (request.responseJSON.PROFILER && $("#template-profiler").length){
                $("#PROFILER").jqotepre($("#template-profiler"), request.responseJSON.PROFILER);
            }
        }
    }

});
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    //console.log(settings.url.indexOf("true"))



    toastr['error'](thrownError,"Ajax Error: "+jqxhr.status,{
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": function(){
            $("#loadingmask").hide()
        },
        "showDuration": "300",
        "hideDuration": "0",
        "timeOut": "0",
        "extendedTimeOut": "0",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      })


    
});