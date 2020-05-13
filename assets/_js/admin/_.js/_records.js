$(document).on("click",".record[data-id]",function(e){
    e.preventDefault();

    $.state.set("id",$(this).attr("data-id"));
    getData();

});