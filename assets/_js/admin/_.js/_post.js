$(document).on("submit","#page-form",function(e){
    e.preventDefault();
    $("#loadingmask").show();
    if (typeof __submit === "function") {
        __submit();
    }

        var $this = $(this);
    var data = $this.serialize();

    $.post("?id="+$.state.get("id"),data,function(response){
        validationErrors(response, $this, function(){
            toastr["success"]("Saving the form", "Success");
           $.state.remove("id");
           getData();
        //    $("#loadingmask").hide();
        }, function(){
            $("#loadingmask").hide();
            toastr["error"]("Saving the form", "Issues were found");
        });

    })
});
