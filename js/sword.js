
function recuperarValores() 
{           
  entregasSeleccionadas = [];
  
  $('input[name="selectedusers"]:checked').each (function() {  
       
       entregasSeleccionadas.push($(this).val());       
      
  });
  
  return entregasSeleccionadas;
}

function enviar(course_id,assignment_id, swordid)
{

  submissions =recuperarValores();
  if (submissions.length>0) {
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
	  location.reload(true);
     }
  );
  } else { 
      alert("No ha seleccionado ninguna entrega");
  }
  
  
}
