;Dropzone.autoDiscover=!1;$.fn.textWidth=function(){var t=$(this).html(),e='<span>'+t+'</span>';$(this).html(e);var i=$(this).find('span:first').width();$(this).html(t);return i};$('.rounded-title').each(function(){var t=$(this).textWidth();$(this).width(t+4);$(this).height(t);$(this).css('margin-top','-'+$(this).outerHeight()/2+'px');var i=$(this).hasClass('rounded-title-login-choice')?'h-line h-line-login-choice':'h-line';$(this).before('<div class="'+i+'" style="margin-top:'+$(this).outerHeight()/2+'px;"></div>')});$('.scroll').click(function(t){t.preventDefault();$('html, body').animate({scrollTop:$($(this).attr('href')).offset().top},500)});$(function(){$('[data-toggle="popover"]').popover({'html':!0});$('[data-toggle="popover"]').on('click',function(t){t.preventDefault();return!0});$('[data-toggle="tooltip"]').tooltip();$('body').on('click',function(t){if($(t.target).data('toggle')!=='popover'&&$(t.target).parents('.popover.in').length===0){$('[data-toggle="popover"]').popover('hide')}});$.fn.extend({attach_notifications_behaviour:function(){this.off();return this.each(function(){$(this).click(function(t){t.preventDefault();$('#notifs-modal .loader').show();$('#notifs-modal-content').html('');$('#notifs-modal').modal('show');$.get($(this).attr('href'),function(t){$('.loader').hide();$('#notifs-modal-content').html(t);$(this).closest('tr.notification-preview').removeClass('table-warning')})})})}});$('.notification-trigger').attach_notifications_behaviour();var i=function(t){var o=t.match(/^url\(["']?(.+?)["']?\)$/),i=new $.Deferred();if(o){var e=new Image();e.onload=i.resolve;e.onerror=i.reject;e.src=o[1]}
else{i.reject()};return i.then(function(){return{width:this.width,height:this.height}})};function t(t,e,n){var a='url(\'http://i.ytimg.com/vi/'+e+'/hqdefault.jpg\')';t.css('background-image',a);var o='100%';i(a).then(function(i){o=Math.min(i.width,t.outerWidth())}).always(function(){if(n!=''){t.append('<div class="youtube-caption">'+n+'</div>')};t.append('<div class="play"></div>');t.find('.play').css('width',o).css('bottom',t.find('.youtube-caption').outerHeight()/2);t.find('.youtube-caption').css('width',o)});$(document).delegate('#'+e,'click',function(){var i='https://www.youtube.com/embed/'+e+'?autoplay=1&autohide=1';if(t.data('params'))i+='&'+t.data('params');var o=$('<iframe/>',{'frameborder':'0','src':i,'width':t.width(),'height':t.height()});t.replaceWith(o)})};$('.youtube').each(function(){var i=this.id,e=$(this);$.getJSON('https://www.googleapis.com/youtube/v3/videos',{key:'AIzaSyBMt1U3tTxBt4AtRR4BAwo4knEQXJf4y-A',part:'snippet,statistics',id:i}).done(function(o){if(o.items.length===0){t(e,i,'')}
else{t(e,i,o.items[0].snippet.title)}}).fail(function(){t(e,i,'')})})});