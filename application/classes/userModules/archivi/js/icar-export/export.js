$(document).ready(function(){
    $('#action').on('click',function(e){
      e.preventDefault();  
      var formData = new FormData();
      formData.append('ids', $('#ids').val());
      formData.append('exportSelected', $('#exportSelected').val());
      formData.append('exportTitle', $('#exportTitle').val());
      formData.append('exportFormat', $('#exportFormat').val());
      formData.append('exportAutBib', $('#exportAutBib').prop('checked'));
      formData.append('exportEmail', $('#exportEmail').val());
      formData.append('exportMets', $('#exportMets').val());
      formData.append('exportType', $('#exportType').val());
  
      var configFile = $('#configFile').prop('files');
      if (configFile !== undefined) {
        formData.append('configFile', configFile[0]);
      }
  
      $.ajax({
          url: Pinax.ajaxUrl + '&controllerName=metafad.gestioneDati.boards.controllers.ajax.Export',
          type: 'POST',
          cache: false,
          contentType: false,
          processData: false,
          data: formData,
          success: function(data) {
              if(data.url!=null){
                window.top.location.href = data.url;
              }else{
                alert(data.msg);
              }
          },
          error: function() {
              alert('Si è verificato un problema.');
          }
      });
  
    });
  
  });
  