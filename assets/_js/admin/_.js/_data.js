
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