;$(function(){$('#modal-dialog').dialog({modal:!0,hide:'fade',show:'fade',autoOpen:!1});$('#update-motivations-button').click(function(a){a.preventDefault();var t=$('#motivations-textarea'),n=t.val(),o=$(this).attr('href');$.ajax({url:o,data:{motivations:n},method:'post',beforeSend:function(){$('#modal-dialog').dialog('open');$('#modal-dialog').html($('.loader')[0].outerHTML);$('#modal-dialog .loader').show()},success:function(a){$('#modal-dialog').html('<p>Vos motivations ont bien été mises à jour.</p>');$('.contract-motivations').html(a)},error:function(){$('#modal-dialog').html('<p>OUPS, erreur imprévue</p>')}})});var t=0;$('.incr-btn').each(function(){var a=$(this)});function n(){var a=0;$('.quantity.form-control').each(function(){a+=parseInt($(this).val())});t=a};$('.quantity.form-control').on('change',function(){a()});function a(){n();if(t==0){$('#app_bundle_contract_fan_type_submit').attr('disabled','disabled')}
else{$('#app_bundle_contract_fan_type_submit').attr('disabled',null)}};a();$('.incr-btn').on('click',function(i){var t=$(this),n=t.parent().find('.quantity').val();t.parent().find('.incr-btn[data-action="decrease"]').removeClass('inactive');if(t.data('action')=='increase'){if(n<t.data('max')){var o=parseFloat(n)+1}
else{o=n}}
else{if(n>0){var o=parseFloat(n)-1}
else{o=0;t.addClass('inactive')}};t.parent().find('.quantity').val(o);a();i.preventDefault()})});