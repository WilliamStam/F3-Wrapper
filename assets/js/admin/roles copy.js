$(document).ready(function(){
    $("#testing-get").click(function(){
        $("#loadingmask").show();
        $.getData("?",{},function(data){
            render(data);
        })
    })
    $("#testing-post").click(function(){
        $("#loadingmask").show();
        $.postData("?",{},function(data){
            render(data);
        })
    })
});

function render(data){
    $("#testing").jqotesub($("#template-content"), data);
    $("#loadingmask").hide();

    // $("#profiler-button").click()
}