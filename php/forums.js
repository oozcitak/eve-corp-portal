function ShowReplyButtons(id)
{
  var el = ObjFromID('buttons' + id);
  el.style.visibility = "visible";
}

function HideReplyButtons(id)
{
  var el = ObjFromID('buttons' + id);
  el.style.visibility = "hidden";
}

function ToggleAdminLinks()
{
	var els = ObjFromID('adminlink');
	var elo = ObjFromID('adminlink2');
	var elp = ObjFromID('adminlinks');
    
  if(elp.style.display == "block")
  {
    els.style.display = "inline";
    elo.style.display = "none";
    elp.style.display = "none";
  }
  else
  {
    var pos = findPos(els);
    var h = els.offsetHeight + 1;
    els.style.display = "none";
    elo.style.display = "inline";
    elp.style.display = "block";
    
    elp.x = pos[0];
    elp.y = pos[1] + h;
    elp.style.left = pos[0]+'px';
    elp.style.top = (pos[1] + h)+'px';
  }
}

function ObjFromID(id)
{
	if(document.getElementById) 
		return document.getElementById(id);
	else if(document.all) 
		return document.all[id]; 
	else if(document.layers) 
		return document.layers[id];
}

// From Quirksmode.org
// http://www.quirksmode.org/js/findpos.html
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while ((obj = obj.offsetParent) != null);
	}
	return [curleft,curtop];
}
