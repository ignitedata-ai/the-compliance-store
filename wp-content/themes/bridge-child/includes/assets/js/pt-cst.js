
jQuery( "button.search-submit" ).click(function() {;
        jQuery(".ps-sort").attr('value', '');
        jQuery('select.ps-sort option:selected').val('');

        if(getUrlParameter('order') =='asc' && getUrlParameter('orderby') =='date'){
            jQuery(".ps-sort").attr('value', 'asc');
            jQuery('select.ps-sort option:selected').val('date');    
        }
        else if(getUrlParameter('order') =='asc' && getUrlParameter('orderby') =='title'){
            jQuery(".ps-sort").attr('value', 'asc');
            jQuery('select.ps-sort option:selected').val('title');    
        }
        else if(getUrlParameter('order') =='desc' && getUrlParameter('orderby') =='date'){
            jQuery(".ps-sort").attr('value', 'desc');
            jQuery('select.ps-sort option:selected').val('date');    
        }
        else if(getUrlParameter('order') =='desc' && getUrlParameter('orderby') =='title'){
            jQuery(".ps-sort").attr('value', 'desc');
            jQuery('select.ps-sort option:selected').val('title');    
        }
        
  });

  var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};