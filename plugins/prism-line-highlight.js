!function(){function e(e,t){return Array.prototype.slice.call((t||document).querySelectorAll(e))}function t(e,t){return t=" "+t+" ",(" "+e.className+" ").replace(/[\n\t]/g," ").indexOf(t)>-1}function n(e,n,r){for(var i,a=n.replace(/\s+/g,"").split(","),l=+e.getAttribute("data-line-offset")||0,o=parseFloat(getComputedStyle(e).lineHeight),d=0;i=a[d++];){i=i.split("-");var c=+i[0],h=+i[1]||c,s=document.createElement("div");s.textContent=Array(h-c+2).join(" \r\n"),s.className=(r||"")+" line-highlight",t(e,"line-numbers")||(s.setAttribute("data-start",c),h>c&&s.setAttribute("data-end",h)),s.style.top=(c-l-1)*o+"px",t(e,"line-numbers")?e.appendChild(s):(e.querySelector("code")||e).appendChild(s)}}function r(){var t=location.hash.slice(1);e(".temporary.line-highlight").forEach(function(e){e.parentNode.removeChild(e)});var r=(t.match(/\.([\d,-]+)$/)||[,""])[1];if(r&&!document.getElementById(t)){var i=t.slice(0,t.lastIndexOf(".")),a=document.getElementById(i);a&&(a.hasAttribute("data-line")||a.setAttribute("data-line",""),n(a,r,"temporary "),document.querySelector(".temporary.line-highlight").scrollIntoView())}}if(window.Prism){var i=(crlf=/\r?\n|\r/g,0);Prism.hooks.add("after-highlight",function(t){var a=t.element.parentNode,l=a&&a.getAttribute("data-line");a&&l&&/pre/i.test(a.nodeName)&&(clearTimeout(i),e(".line-highlight",a).forEach(function(e){e.parentNode.removeChild(e)}),n(a,l),i=setTimeout(r,1))}),addEventListener("hashchange",r)}}();
