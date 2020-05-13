
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