!function(e){e.fn.extend({slimScroll:function(i){var o={width:"auto",height:"250px",size:"7px",color:"#000",position:"right",distance:"1px",start:"top",opacity:.4,alwaysVisible:!1,disableFadeOut:!1,railVisible:!1,railColor:"#333",railOpacity:.2,railDraggable:!0,railClass:"slimScrollRail",barClass:"slimScrollBar",wrapperClass:"slimScrollDiv",allowPageScroll:!1,wheelStep:20,touchScrollStep:200,borderRadius:"7px",railBorderRadius:"7px"},s=e.extend(o,i);return this.each(function(){function o(t){if(c){var t=t||window.event,i=0;t.wheelDelta&&(i=-t.wheelDelta/120),t.detail&&(i=t.detail/3);var o=t.target||t.srcTarget||t.srcElement;e(o).closest("."+s.wrapperClass).is(y.parent())&&r(i,!0),t.preventDefault&&!m&&t.preventDefault(),m||(t.returnValue=!1)}}function r(e,t,i){m=!1;var o=e,r=y.outerHeight()-E.outerHeight();if(t&&(o=parseInt(E.css("top"))+e*parseInt(s.wheelStep)/100*E.outerHeight(),o=Math.min(Math.max(o,0),r),o=e>0?Math.ceil(o):Math.floor(o),E.css({top:o+"px"})),f=parseInt(E.css("top"))/(y.outerHeight()-E.outerHeight()),o=f*(y[0].scrollHeight-y.outerHeight()),i){o=e;var a=o/y[0].scrollHeight*y.outerHeight();a=Math.min(Math.max(a,0),r),E.css({top:a+"px"})}y.scrollTop(o),y.trigger("slimscrolling",~~o),l(),n()}function a(){g=Math.max(y.outerHeight()/y[0].scrollHeight*y.outerHeight(),w),E.css({height:g+"px"});var e=g==y.outerHeight()?"none":"block";E.css({display:e})}function l(){if(a(),clearTimeout(d),f==~~f){if(m=s.allowPageScroll,v!=f){var e=0==~~f?"top":"bottom";y.trigger("slimscroll",e)}}else m=!1;if(v=f,g>=y.outerHeight())return void(m=!0);E.stop(!0,!0).fadeIn("fast"),s.railVisible&&S.stop(!0,!0).fadeIn("fast")}function n(){s.alwaysVisible||(d=setTimeout(function(){s.disableFadeOut&&c||h||u||(E.fadeOut("slow"),S.fadeOut("slow"))},1e3))}var c,h,u,d,p,g,f,v,b="<div></div>",w=30,m=!1,y=e(this);if(y.parent().hasClass(s.wrapperClass)){var x=y.scrollTop();if(E=y.closest("."+s.barClass),S=y.closest("."+s.railClass),a(),e.isPlainObject(i)){if("height"in i&&"auto"==i.height){y.parent().css("height","auto"),y.css("height","auto");var C=y.parent().parent().height();y.parent().css("height",C),y.css("height",C)}if("scrollTo"in i)x=parseInt(s.scrollTo);else if("scrollBy"in i)x+=parseInt(s.scrollBy);else if("destroy"in i)return E.remove(),S.remove(),void y.unwrap();r(x,!1,!0)}}else if(!(e.isPlainObject(i)&&"destroy"in i)){s.height="auto"==s.height?y.parent().height():s.height;var H=e(b).addClass(s.wrapperClass).css({position:"relative",overflow:"hidden",width:s.width,height:s.height});y.css({overflow:"hidden",width:s.width,height:s.height});var S=e(b).addClass(s.railClass).css({width:s.size,height:"100%",position:"absolute",top:0,display:s.alwaysVisible&&s.railVisible?"block":"none","border-radius":s.railBorderRadius,background:s.railColor,opacity:s.railOpacity,zIndex:90}),E=e(b).addClass(s.barClass).css({background:s.color,width:s.size,position:"absolute",top:0,opacity:s.opacity,display:s.alwaysVisible?"block":"none","border-radius":s.borderRadius,BorderRadius:s.borderRadius,MozBorderRadius:s.borderRadius,WebkitBorderRadius:s.borderRadius,zIndex:99}),R="right"==s.position?{right:s.distance}:{left:s.distance};S.css(R),E.css(R),y.wrap(H),y.parent().append(E),y.parent().append(S),s.railDraggable&&E.bind("mousedown",function(i){var o=e(document);return u=!0,t=parseFloat(E.css("top")),pageY=i.pageY,o.bind("mousemove.slimscroll",function(e){currTop=t+e.pageY-pageY,E.css("top",currTop),r(0,E.position().top,!1)}),o.bind("mouseup.slimscroll",function(e){u=!1,n(),o.unbind(".slimscroll")}),!1}).bind("selectstart.slimscroll",function(e){return e.stopPropagation(),e.preventDefault(),!1}),S.hover(function(){l()},function(){n()}),E.hover(function(){h=!0},function(){h=!1}),y.hover(function(){c=!0,l(),n()},function(){c=!1,n()}),y.bind("touchstart",function(e,t){e.originalEvent.touches.length&&(p=e.originalEvent.touches[0].pageY)}),y.bind("touchmove",function(e){if(m||e.originalEvent.preventDefault(),e.originalEvent.touches.length){r((p-e.originalEvent.touches[0].pageY)/s.touchScrollStep,!0),p=e.originalEvent.touches[0].pageY}}),a(),"bottom"===s.start?(E.css({top:y.outerHeight()-E.outerHeight()}),r(0,!0)):"top"!==s.start&&(r(e(s.start).position().top,null,!0),s.alwaysVisible||E.hide()),function(e){window.addEventListener?(e.addEventListener("DOMMouseScroll",o,!1),e.addEventListener("mousewheel",o,!1)):document.attachEvent("onmousewheel",o)}(this)}}),this}}),e.fn.extend({slimscroll:e.fn.slimScroll})}(jQuery);
