
function validationErrors(response, $form, success_handler, error_handler){
    

    $form.find(".is-invalid").removeClass('is-invalid');
    $form.find(".invalid-feedback").remove();

    if ( !$.isEmptyObject(response['errors']) ){

        var toastr_msg = [];

        $.each(response['errors'],function(field,errors){
            var $field = $("[name='"+field+"'",$form);
            var $label = $("label[for='"+field+"'",$form);

            $field.addClass("is-invalid");

            $errorTextTemplate = $("<div/>")
            $errorTextTemplate.addClass("invalid-feedback");

            $.each(errors,function(k,error){
                if (error==""){
                    error = $label.text() + " is Required";
                }
                toastr_msg.push(error);

                $errorTextTemplate.clone().text(error).insertAfter($field);
            })
        })
        error_handler();
    } else {
        
       
        success_handler();
    }
}
;/*********************************/;

	$["delete"] = function(url, callback, type) {
		return jQuery.ajax({
			type: "DELETE",
			url: url,
			success: callback,
			dataType: type
		});
    };
    
$(document).on("click",".delete-btn",function(e){
    e.preventDefault();
    

    if (confirm("Are you sure you want to delete this record?")){
        $("#loadingmask").show();
        $.delete("?id="+$.state.get("id"),function(response){

            if( Object.keys(response.errors).length == 0 ) {
                toastr["success"]("Deleting the record", "Success");
                $.state.remove("id");
                getData();
            } else {
                toastr["error"]("Deleting the record", "Issues were found");
            }
    
            $("#loadingmask").hide();
        })
    }
    
});

;/*********************************/;
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

;/*********************************/;
$(document).on("submit",".toolbar-form",function(e){
    e.preventDefault();
    var $this = $(this);
    var data = $this.serializeArray();

    for (var i in data){
        $.state.set(data[i].name,data[i].value);
    }

    getData();

});

$(document).on("reset",".toolbar-form",function(e){
    e.preventDefault();
    var $this = $(this);
    var data = $this.serializeArray();

    for (var i in data){
        $.state.remove(data[i].name);
    }

    getData();

});

;/*********************************/;

function getData(){
    $("#loadingmask").show();
    $.getData("?",$.state.get(),function(data){
            render(data);
    });
}
function render(data){

    var continue_render = true;
	if (typeof __render === "function") {
		continue_render = __render(data);
	}

	if (continue_render) {
		if ( data.id ) {
			$("#page-content").jqotesub($("#template-content-details"), data);
		} else {
			$("#page-content").jqotesub($("#template-content-list"), data);
		}



		if (typeof __render_after === "function") {
			 __render_after(data);
		}
		$("#loadingmask").hide();


    }
    

}
;/*********************************/;
$(document).on("click",".record[data-id]",function(e){
    e.preventDefault();

    $.state.set("id",$(this).attr("data-id"));
    getData();

});