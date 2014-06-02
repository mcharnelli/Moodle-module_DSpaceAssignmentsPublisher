
function recuperarValores() 
{           
  entregasSeleccionadas = [];
  
  $('input[class="usercheckbox"]:checked').each (function() {  
       
       entregasSeleccionadas.push($(this).val());       
      
  });
  
  return entregasSeleccionadas;
}

function enviar(course_id,assignment_id, swordid)

{
  
  submissions =recuperarValores();
   if (submissions.length>0) {
     $("body").addClass("loading"); 
     $.post( "sendToRepo22.php",
       {id:course_id,
        assignment_id:assignment_id,
        submissions:submissions,
        sword_cm_id:swordid 
      },
        function(data, textStatus, jqXHR) {
             $("body").removeClass("loading"); 
	    alert(data);	 
	     location.reload(true);
        }
    );
   } else {
       $.post("message.php", 
	     {
	         str:"non_selected"
	     },
	     function(data, textStatus, jqXHR) {         
	         alert(data);		 
              });     
   }
  
  
}
