
function getData() {
    $("#loadingmask").show();
    $.getData("?", $.state.get(), function (data) {
        render(data);
    });
}
function render(data) {


    $("#page-content").jqotesub($("#template-content"), data);
    $("#loadingmask").hide();


}