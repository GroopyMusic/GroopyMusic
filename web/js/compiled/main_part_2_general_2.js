;Dropzone.autoDiscover=!1;$.fn.textWidth=function(){var o=$(this).html(),i='<span>'+o+'</span>';$(this).html(i);var t=$(this).find('span:first').width();$(this).html(o);return t};$('.rounded-title').each(function(){var o=$(this).textWidth();$(this).width(o+4);$(this).height(o);$(this).css('margin-top','-'+$(this).outerHeight()/2+'px');var t=$(this).hasClass('rounded-title-login-choice')?'h-line h-line-login-choice':'h-line';$(this).before('<div class="'+t+'" style="margin-top:'+$(this).outerHeight()/2+'px;"></div>')});$('.scroll').click(function(o){o.preventDefault();$('html, body').animate({scrollTop:$($(this).attr('href')).offset().top},500)});$(function(){$('[data-toggle="popover"]').popover({'html':!0});$('[data-toggle="popover"]').on('click',function(o){o.preventDefault();return!0});$('[data-toggle="tooltip"]').tooltip();$('body').on('click',function(o){if($(o.target).data('toggle')!=='popover'&&$(o.target).parents('.popover.in').length===0){$('[data-toggle="popover"]').popover('hide')}});$.fn.extend({attach_notifications_behaviour:function(){this.off();return this.each(function(){$(this).click(function(o){o.preventDefault();$('#notifs-modal .loader').show();$('#notifs-modal-content').html('');$('#notifs-modal').modal('show');$.get($(this).attr('href'),function(o){$('.loader').hide();$('#notifs-modal-content').html(o);$(this).closest('tr.notification-preview').removeClass('table-warning')})})})}});$('.notification-trigger').attach_notifications_behaviour();var t=function(o){var n=o.match(/^url\(["']?(.+?)["']?\)$/),t=new $.Deferred();if(n){var i=new Image();i.onload=t.resolve;i.onerror=t.reject;i.src=n[1]}
else{t.reject()};return t.then(function(){return{width:this.width,height:this.height}})};function o(o,i,e){var s='url(\'https://i.ytimg.com/vi/'+i+'/hqdefault.jpg\')';o.css('background-image',s);var n='100%';t(s).then(function(t){n=Math.min(t.width,o.outerWidth())}).always(function(){if(e!=''){o.append('<div class="youtube-caption">'+e+'</div>')};o.append('<div class="play"></div>');o.find('.play').css('width',n).css('bottom',o.find('.youtube-caption').outerHeight()/2);o.find('.youtube-caption').css('width',n)});$(document).delegate('#'+i,'click',function(){var t='https://www.youtube.com/embed/'+i+'?autoplay=1&autohide=1';if(o.data('params'))t+='&'+o.data('params');var n=$('<iframe/>',{'frameborder':'0','src':t,'width':o.width(),'height':o.height()});o.replaceWith(n)})};$('.youtube').each(function(){var t=this.id,i=$(this);$.getJSON('https://www.googleapis.com/youtube/v3/videos',{key:'AIzaSyBMt1U3tTxBt4AtRR4BAwo4knEQXJf4y-A',part:'snippet,statistics',id:t}).done(function(n){if(n.items.length===0){o(i,t,'')}
else{o(i,t,n.items[0].snippet.title)}}).fail(function(){o(i,t,'')})})});function addEmailInput(){var o=$('#sponsorship-modal-email-inputs-div'),t='<div class="form-group"><input type="email" class="sponsorship-modal-email-inputs sponsorship-modal-added-inputs form-control" placeholder="Entrer une adresse email"></div>';o.append(t)};function removeEmailInput(){$('.sponsorship-modal-added-inputs').last().remove()};function displaySponsorshipInvitationModal(){$('#sponsorship-invitations-modal').on('hidden.bs.modal',function(){$('#sponsorship-modal-form')[0].reset();$('.sponsorship-modal-added-inputs').each(function(){$(this).remove()});hideSponsoringAlert()}).modal();$('#sponsorship-modal-form').on('submit',function(o){o.preventDefault();sendSponsorshipInvitation()})};function showSponsorshipLoader(){$('#sponsorship-modal-content').hide();$('#sponsorship-modal-loader').find('.loader').first().show()};function hideSponsorshipLoader(){$('#sponsorship-modal-content').show();$('#sponsorship-modal-loader').find('.loader').first().hide()};function showSponsorshipSuccess(o){var t=$('#sponsorship-modal-alert-success');t.append(o);t.attr('hidden',!1)};function showSponsorshipDanger(o){console.log('ici');var t=$('#sponsorship-modal-alert-danger');t.append(o);t.attr('hidden',!1)};function hideSponsoringAlert(){$('#sponsorship-modal-alert-success').attr('hidden',!0);$('#sponsorship-modal-alert-danger').attr('hidden',!0)};function sendSponsorshipInvitation(){showSponsorshipLoader();var o=[],t=$('#sponsorship-modal-textarea').text(),i=$('#sponsorship-modal-form').attr('action');$('.sponsorship-modal-email-inputs').each(function(){o.push($(this).val())});console.log(o);$.post(i,{emails:o,textarea:t},function(o){console.log(o);showSponsorshipSuccess(o);hideSponsorshipLoader()}).fail(function(o){console.log(o);showSponsorshipDanger(o.responseText);hideSponsorshipLoader()})};