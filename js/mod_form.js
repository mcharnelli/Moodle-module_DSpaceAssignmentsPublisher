function getCollections(selected){
 url = $("#id_base_url").val();
 $("#id_url_selector").empty();
 $.ajax({
    type: "POST",
     url: "../mod/sword/getCollections.php",
     processData : true,
     data: {url: url},
     dataType: "json",
     success: function(json) {       
       
	  for (var key in json) {
	      collection = json[key];
	      optionBegin = '<option value="' + url + '/sword/deposit/' + collection["handle"] + '"';
	      optionEnd   = collection["name"] +"</option>";
	      if ((selected != null) &&  (selected == collection["handle"])) {
	        optionBegin += ' selected="selected">';
	      } else {
		optionBegin += '>';
	      }
	      $("#id_url_selector").append(optionBegin + optionEnd);
	  }        
	  $("#id_url_selector").change();
	  $('#id_url_selector').prop('disabled', false);
	  
   } ,
     error: function(x,y,z) {
         $.post("../mod/sword/message.php", 
	     {
	         str:"cannot_get_collections"
	     },
	     function(data, textStatus, jqXHR) {         
	         alert(data);		 
              });  
          $("#accordion").children("h3").eq(1).click();
	 
          
     } 
  });

}
$(document).ready( function() {  
  $( "#accordion" ).accordion();

  $("#id_url_selector").change( function() {
       $('input[name="url"]').val($("#id_url_selector option:selected").val());
  });
  $("#fitem_id_find").appendTo("#fitem_id_base_url");
  $('#id_url_selector').prop('disabled', true);

  
  var url = $('input[name="url"]').val();
  if (url != "") {
     var pathArray =  url.split("/sword/deposit/" );
     $("#id_base_url").val(pathArray[0]); 
     getCollections(pathArray[1]);
     
     
     //.each(function()  {
        //alert($(this).val());
     //});
     
  }
  
});
