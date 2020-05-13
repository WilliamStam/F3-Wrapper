
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
