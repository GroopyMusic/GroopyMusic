;$(function(){var t=0;$('.incr-btn').each(function(){var a=$(this)});function n(){var a=0;$('.quantity.form-control').each(function(){a+=parseInt($(this).val())});t=a};$('.quantity.form-control').on('change',function(){a()});function a(){n();if(t==0){$('#app_bundle_contract_fan_type_submit').attr('disabled','disabled')}
else{$('#app_bundle_contract_fan_type_submit').attr('disabled',null)}};a();$('.incr-btn').on('click',function(e){var t=$(this),n=t.parent().find('.quantity').val();t.parent().find('.incr-btn[data-action="decrease"]').removeClass('inactive');if(t.data('action')=='increase'){if(n<t.data('max')){var i=parseFloat(n)+1}
else{i=n}}
else{if(n>0){var i=parseFloat(n)-1}
else{i=0;t.addClass('inactive')}};t.parent().find('.quantity').val(i);a();e.preventDefault()})});