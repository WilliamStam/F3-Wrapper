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
