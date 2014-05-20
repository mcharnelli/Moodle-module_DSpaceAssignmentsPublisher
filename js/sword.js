
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
   $("body").addClass("loading"); 
  $.post( "sendToRepo.php",
    {id:course_id,
     assignment_id:assignment_id,
     submissions:submissions,
     swordid:swordid 
    },
     function(data, textStatus, jqXHR) {
           $("body").removeClass("loading"); 
	  alert(data);	 
     }
  );
  
  
}
