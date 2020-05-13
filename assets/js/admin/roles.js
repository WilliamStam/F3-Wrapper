$(document).ready(function(){
    $(document).on("click","#testing-get", function(){
        getData();
    })
    $(document).on("click","#testing-post", function(){
        $("#loadingmask").show();
        $.postData("?",{},function(data){
            render(data);
        })
    })
});

