;Dropzone.autoDiscover=!1;$.fn.textWidth=function(){var t=$(this).html(),o='<span>'+t+'</span>';$(this).html(o);var i=$(this).find('span:first').width();$(this).html(t);return i};$('.rounded-title').each(function(){var t=$(this).textWidth();$(this).width(t+4);$(this).height(t);$(this).css('margin-top','-'+$(this).outerHeight()/2+'px');$(this).before('<div class="h-line" style="margin-top:'+$(this).outerHeight()/2+'px;"></div>')});$('.scroll').click(function(t){t.preventDefault();$('html, body').animate({scrollTop:$($(this).attr('href')).offset().top},500)});$(function(){$('[data-toggle="popover"]').popover({'html':!0});$('[data-toggle="popover"]').on('click',function(t){t.preventDefault();return!0});$('[data-toggle="tooltip"]').tooltip();$('body').on('click',function(t){if($(t.target).data('toggle')!=='popover'&&$(t.target).parents('.popover.in').length===0){$('[data-toggle="popover"]').popover('hide')}});$.fn.extend({attach_notifications_behaviour:function(){this.off();return this.each(function(){$(this).click(function(t){t.preventDefault();$('#notifs-modal .loader').show();$('#notifs-modal-content').html('');$('#notifs-modal').modal('show');$.get($(this).attr('href'),function(t){$('.loader').hide();$('#notifs-modal-content').html(t);$(this).closest('tr.notification-preview').removeClass('table-warning')})})})}});$('.notification-trigger').attach_notifications_behaviour();$('.youtube').each(function(){$(this).css('background-image','url(http://i.ytimg.com/vi/'+this.id+'/sddefault.jpg)');$(this).append($('<div/>',{'class':'play'}));$(document).delegate('#'+this.id,'click',function(){var t='https://www.youtube.com/embed/'+this.id+'?autoplay=1&autohide=1';if($(this).data('params'))t+='&'+$(this).data('params');var i=$('<iframe/>',{'frameborder':'0','src':t,'width':$(this).width(),'height':$(this).height()});$(this).replaceWith(i)})})});