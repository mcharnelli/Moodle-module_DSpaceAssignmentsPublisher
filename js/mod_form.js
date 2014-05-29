function getCollections(){
 url = $("#id_base_url").val(); 
 $.ajax({
    type: "POST",
     url: "../mod/sword/getCollections.php",
     processData : true,
     data: {url: url},
     dataType: "json",
     success: function(json) {          
	  for (var key in json) {
	      collection = json[key];
	      $("#id_url_selector").append('<option value="' + url + '/sword/deposit/' + collection["handle"] + '">' + collection["name"] +"</option>");
	     
	  }        
	  $("#id_url_selector").change();
   } ,
     error: function(x,y,z) {
          alert("No se han podido obtener las colecciones");
     } 
  });

}
$(document).ready( function() {  
  $("#id_url_selector").change( function() {
       $('input[name="url"]').val($("#id_url_selector option:selected").val());
  });
  $("#fitem_id_find").appendTo("#fitem_id_base_url");
  
  
  var url = $('input[name="url"]').val();
  if (url != "") {
     var pathArray =  url.split("/sword/deposit/" );
     $("#id_base_url").val(pathArray[0]); 
     getCollections();
     console.log($("#id_url_selector > option"));
     
     //.each(function()  {
        //alert($(this).val());
     //});
     
  }
  
});
